from typing import List, Optional, Literal
from pydantic import BaseModel, Field


class ChatMessage(BaseModel):
    role: Literal["user", "assistant"]
    content: str = Field(min_length=1, max_length=4000)


class ChatRequest(BaseModel):
    messages: List[ChatMessage] = Field(min_length=1)
    user_id: Optional[int] = None
    session_id: Optional[str] = None
    user_email: Optional[str] = None


class ChatSuccessResponse(BaseModel):
    success: bool = True
    reply: str
    role: str = "assistant"


class ErrorResponse(BaseModel):
    success: bool = False
    message: str
    code: int
