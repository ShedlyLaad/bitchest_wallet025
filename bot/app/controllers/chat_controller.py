import logging

from fastapi import APIRouter
from fastapi.responses import JSONResponse

from app.schemas import ChatRequest, ChatSuccessResponse
from app.services.groq_service import GroqService, GroqServiceError
from app.services.user_service import UserService

logger = logging.getLogger("bitchest_support_bot")

router = APIRouter()

SYSTEM_PROMPT = """
You are the BitChest Support Bot — the official AI assistant for the BitChest cryptocurrency trading platform.

- Tone: Professional, concise, friendly.
- Language: Respond in the same language the user writes in.
- Never give financial advice or predict prices.
- Keep responses under 120 words unless necessary.
- When the user's portfolio data is provided below, use it directly to answer questions about holdings, balances, and crypto positions.
- Never ask the user to link or update their portfolio when portfolio data is already provided.
"""


def build_chat_router(groq_service: GroqService, user_service: UserService) -> APIRouter:
    @router.post("/chat", response_model=ChatSuccessResponse)
    async def chat(request: ChatRequest):
        logger.debug(
            "Incoming /chat request messages=%s user_id=%s session_id=%s user_email=%s",
            len(request.messages),
            request.user_id,
            request.session_id,
            request.user_email,
        )

        user_context = await user_service.get_user_context(
            user_id=request.user_id,
            session_id=request.session_id,
            user_email=request.user_email,
        )

        personalization_lines = []
        if user_context:
            personalization_lines.append(f"The user's name is {user_context.name}.")
            portfolio = user_context.portfolio_data
            if portfolio and portfolio != "No portfolio data available":
                personalization_lines.append(f"User portfolio (live from BitChest database): {portfolio}.")
                personalization_lines.append(
                    "Use this portfolio data to answer questions about the user's crypto holdings."
                )
            else:
                personalization_lines.append("User portfolio: empty (no active crypto holdings).")
            personalization_lines.append("Always greet the user by name in the first sentence.")
        elif request.user_email:
            personalization_lines.append("The user is authenticated by email.")

        messages = [
            {
                "role": "system",
                "content": f"{SYSTEM_PROMPT}\n\n" + "\n".join(personalization_lines),
            },
            *[{"role": msg.role, "content": msg.content} for msg in request.messages],
        ]

        try:
            reply = await groq_service.chat(messages)
        except GroqServiceError as exc:
            logger.error(
                "Controlled Groq failure code=%s status=%s message=%s",
                exc.code,
                exc.status_code,
                exc.message,
            )
            return JSONResponse(
                status_code=exc.status_code,
                content={
                    "success": False,
                    "message": exc.message,
                    "code": exc.code,
                },
            )
        except Exception as exc:  # noqa: BLE001
            logger.exception("Unhandled chat error: %s", exc)
            return JSONResponse(
                status_code=500,
                content={
                    "success": False,
                    "message": "Internal chatbot error",
                    "code": 1999,
                },
            )

        return ChatSuccessResponse(reply=reply)

    return router
