import logging
from fastapi import FastAPI
from fastapi.exceptions import RequestValidationError
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse

from app.config import get_settings
from app.controllers.chat_controller import build_chat_router
from app.services.groq_service import GroqService
from app.services.user_service import UserService

logging.basicConfig(level=logging.DEBUG, format="%(asctime)s %(levelname)s %(name)s - %(message)s")
logger = logging.getLogger("bitchest_support_bot")

settings = get_settings()

if not settings.groq_api_key:
    logger.error("GROQ_API_KEY is missing in environment; /chat will return controlled configuration errors.")

app = FastAPI(title="BitChest Support Bot API", version="3.0.0")
app.add_middleware(
    CORSMiddleware,
    allow_origins=settings.allowed_origins,
    allow_credentials=True,
    allow_methods=["POST", "GET"],
    allow_headers=["*"],
)

app.include_router(
    build_chat_router(
        groq_service=GroqService(settings),
        user_service=UserService(settings),
    )
)


@app.exception_handler(RequestValidationError)
async def validation_exception_handler(_, exc: RequestValidationError):
    logger.error("Request validation failed: %s", exc.errors())
    return JSONResponse(
        status_code=422,
        content={
            "success": False,
            "message": "Invalid request payload",
            "code": 1400,
        },
    )


@app.exception_handler(Exception)
async def unhandled_exception_handler(_, exc: Exception):
    logger.exception("Unhandled backend exception: %s", exc)
    return JSONResponse(
        status_code=500,
        content={
            "success": False,
            "message": "Internal server error",
            "code": 1500,
        },
    )


@app.get("/health")
async def health_check():
    return {
        "status": "ok",
        "service": "bitchest-support-bot",
        "provider": "groq",
    }
