import os
from dataclasses import dataclass
from dotenv import load_dotenv

load_dotenv()


@dataclass(frozen=True)
class Settings:
    groq_api_key: str
    groq_url: str
    groq_model: str
    allowed_origins: list[str]
    mysql_host: str
    mysql_port: int
    mysql_user: str
    mysql_password: str
    mysql_database: str


def get_settings() -> Settings:
    origins_raw = os.getenv(
        "ALLOWED_ORIGINS",
        "http://localhost:5173,http://127.0.0.1:5173,http://localhost:3000,http://127.0.0.1:3000",
    )
    origins = [origin.strip() for origin in origins_raw.split(",") if origin.strip()]

    return Settings(
        groq_api_key=os.getenv("GROQ_API_KEY", "").strip(),
        groq_url=os.getenv("GROQ_URL", "https://api.groq.com/openai/v1/chat/completions"),
        groq_model=os.getenv("GROQ_MODEL", "llama-3.3-70b-versatile"),
        allowed_origins=origins,
        mysql_host=os.getenv("MYSQL_HOST", "127.0.0.1"),
        mysql_port=int(os.getenv("MYSQL_PORT", "3306")),
        mysql_user=os.getenv("MYSQL_USER", "root"),
        mysql_password=os.getenv("MYSQL_PASSWORD", ""),
        mysql_database=os.getenv("MYSQL_DATABASE", "bitchest"),
    )
