import logging

from fastapi import APIRouter
from fastapi.responses import JSONResponse

from app.prompts import SYSTEM_PROMPT
from app.schemas import ChatRequest, ChatSuccessResponse
from app.services.groq_service import GroqService, GroqServiceError
from app.services.user_service import UserContext, UserService

logger = logging.getLogger("bitchest_support_bot")

router = APIRouter()


def _build_personalization_context(user_context: UserContext | None, user_email: str | None) -> str:
    """Turn the authenticated user's data into system-prompt context lines."""
    if not user_context:
        if user_email:
            return "The user is authenticated by email."
        return ""

    lines = [f"The user's name is {user_context.name}."]

    portfolio = user_context.portfolio_data
    if portfolio and portfolio != "No portfolio data available":
        lines.append(f"User portfolio (live from BitChest database): {portfolio}.")
        lines.append("Use this portfolio data to answer questions about the user's crypto holdings.")
    else:
        lines.append("User portfolio: empty (no active crypto holdings).")

    lines.append("Always greet the user by name in the first sentence.")
    return "\n".join(lines)


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

        personalization = _build_personalization_context(user_context, request.user_email)

        messages = [
            {
                "role": "system",
                "content": f"{SYSTEM_PROMPT}\n\n{personalization}",
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
