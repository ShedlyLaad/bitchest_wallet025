import asyncio
import logging
from dataclasses import dataclass
from typing import Optional

import mysql.connector
from mysql.connector import Error as MySQLError

from app.config import Settings

logger = logging.getLogger("bitchest_support_bot")


@dataclass
class UserContext:
    user_id: Optional[int]
    name: str
    portfolio_data: str


class UserService:
    def __init__(self, settings: Settings):
        self._settings = settings

    async def get_user_context(
        self,
        *,
        user_id: Optional[int] = None,
        session_id: Optional[str] = None,
        user_email: Optional[str] = None,
    ) -> Optional[UserContext]:
        return await asyncio.to_thread(
            self._get_user_context_sync,
            user_id,
            session_id,
            user_email,
        )

    def _connect(self):
        return mysql.connector.connect(
            host=self._settings.mysql_host,
            port=self._settings.mysql_port,
            user=self._settings.mysql_user,
            password=self._settings.mysql_password,
            database=self._settings.mysql_database,
        )

    def _get_user_context_sync(
        self,
        user_id: Optional[int],
        session_id: Optional[str],
        user_email: Optional[str],
    ) -> Optional[UserContext]:
        if user_id is None and not session_id and not user_email:
            return None

        conn = None
        cursor = None
        try:
            conn = self._connect()
            cursor = conn.cursor(dictionary=True)

            users_columns = self._get_table_columns(cursor, "users")
            sessions_columns = self._get_table_columns(cursor, "sessions")

            user = self._find_user(
                cursor,
                users_columns=users_columns,
                sessions_columns=sessions_columns,
                user_id=user_id,
                session_id=session_id,
                user_email=user_email,
            )
            if not user:
                return None

            portfolio_data = self._extract_portfolio(user)
            if not portfolio_data:
                portfolio_data = self._load_portfolio_from_tables(cursor, user["id"])

            return UserContext(
                user_id=user.get("id"),
                name=(user.get("name") or "User").strip() or "User",
                portfolio_data=portfolio_data or "No portfolio data available",
            )
        except MySQLError as exc:
            logger.exception("MySQL error while fetching user context: %s", exc)
            return None
        finally:
            if cursor:
                cursor.close()
            if conn:
                conn.close()

    def _find_user(
        self,
        cursor,
        *,
        users_columns: set[str],
        sessions_columns: set[str],
        user_id,
        session_id,
        user_email,
    ):
        if not users_columns:
            return None

        select_fields = ["id"]
        if "name" in users_columns:
            select_fields.append("name")
        if "email" in users_columns:
            select_fields.append("email")
        if "portfolio" in users_columns:
            select_fields.append("portfolio")

        select_sql = ", ".join(select_fields)

        if user_id is not None:
            cursor.execute(
                f"SELECT {select_sql} FROM users WHERE id = %s LIMIT 1",
                (user_id,),
            )
            user = cursor.fetchone()
            if user:
                return user

        if session_id and sessions_columns and "user_id" in sessions_columns:
            session_identifier_clause = []
            params = []
            if "id" in sessions_columns:
                session_identifier_clause.append("s.id = %s")
                params.append(session_id)
            if "session_id" in sessions_columns:
                session_identifier_clause.append("s.session_id = %s")
                params.append(session_id)

            if not session_identifier_clause:
                return None

            cursor.execute(
                f"""
                SELECT u.{", u.".join(select_fields)}
                FROM sessions s
                JOIN users u ON u.id = s.user_id
                WHERE {" OR ".join(session_identifier_clause)}
                LIMIT 1
                """,
                tuple(params),
            )
            user = cursor.fetchone()
            if user:
                return user

        if user_email and "email" in users_columns:
            cursor.execute(
                f"SELECT {select_sql} FROM users WHERE email = %s LIMIT 1",
                (user_email,),
            )
            return cursor.fetchone()

        return None

    @staticmethod
    def _extract_portfolio(user_row: dict) -> str:
        value = user_row.get("portfolio")
        if value is None:
            return ""
        if isinstance(value, str):
            return value.strip()
        return str(value)

    @staticmethod
    def _table_exists(cursor, table_name: str) -> bool:
        cursor.execute("SHOW TABLES LIKE %s", (table_name,))
        return cursor.fetchone() is not None

    def _get_table_columns(self, cursor, table_name: str) -> set[str]:
        if not self._table_exists(cursor, table_name):
            return set()
        cursor.execute(f"SHOW COLUMNS FROM {table_name}")
        rows = cursor.fetchall() or []
        return {str(row.get("Field", "")).strip() for row in rows if row.get("Field")}

    def _load_portfolio_from_tables(self, cursor, user_id: int) -> str:
        if not self._table_exists(cursor, "portfolios"):
            return ""

        portfolio_columns = self._get_table_columns(cursor, "portfolios")
        if "user_id" not in portfolio_columns:
            return ""

        if self._table_exists(cursor, "crypto_currencies") and self._table_exists(cursor, "transactions"):
            tx_columns = self._get_table_columns(cursor, "transactions")
            if {"portfolio_id", "type", "quantity"}.issubset(tx_columns):
                cursor.execute(
                    """
                    SELECT
                        cc.symbol,
                        cc.name AS crypto_name,
                        p.total_crypto_value,
                        COALESCE(SUM(CASE WHEN t.type = 'buy' THEN t.quantity ELSE 0 END), 0) AS buy_qty,
                        COALESCE(SUM(CASE WHEN t.type = 'sell' THEN t.quantity ELSE 0 END), 0) AS sell_qty
                    FROM portfolios p
                    INNER JOIN crypto_currencies cc ON cc.id = p.crypto_currency_id
                    LEFT JOIN transactions t ON t.portfolio_id = p.id
                    WHERE p.user_id = %s
                    GROUP BY p.id, cc.symbol, cc.name, p.total_crypto_value
                    ORDER BY cc.symbol
                    """,
                    (user_id,),
                )
                rows = cursor.fetchall() or []
                holdings: list[str] = []
                for row in rows:
                    buy_qty = float(row.get("buy_qty") or 0)
                    sell_qty = float(row.get("sell_qty") or 0)
                    quantity = max(0.0, buy_qty - sell_qty)
                    if quantity <= 0:
                        continue
                    symbol = row.get("symbol") or "?"
                    crypto_name = row.get("crypto_name") or symbol
                    invested = float(row.get("total_crypto_value") or 0)
                    holdings.append(
                        f"{symbol} ({crypto_name}): {quantity:.8f} units held, "
                        f"EUR {invested:.2f} invested value"
                    )
                if holdings:
                    return "; ".join(holdings)

        if "total_crypto_value" in portfolio_columns and self._table_exists(cursor, "crypto_currencies"):
            cursor.execute(
                """
                SELECT cc.symbol, cc.name AS crypto_name, p.total_crypto_value
                FROM portfolios p
                INNER JOIN crypto_currencies cc ON cc.id = p.crypto_currency_id
                WHERE p.user_id = %s AND p.total_crypto_value > 0
                ORDER BY cc.symbol
                """,
                (user_id,),
            )
            rows = cursor.fetchall() or []
            if rows:
                return "; ".join(
                    f"{r.get('symbol')} ({r.get('crypto_name')}): EUR {float(r.get('total_crypto_value') or 0):.2f} invested"
                    for r in rows
                )

        return ""
