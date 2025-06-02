import os
from fastapi import FastAPI, HTTPException, status
from pydantic import BaseModel
from typing import List, Optional
from transformers import AutoTokenizer
from optimum.onnxruntime import ORTModelForFeatureExtraction
import numpy as np

model_name = os.getenv('MODEL_NAME', 'multilingual-e5-small')
ONNX_MODEL_PATH = f"./onnx/{model_name}"
MODEL_ID = os.getenv("MODEL_ID", f"intfloat/{model_name}")

try:
    tokenizer = AutoTokenizer.from_pretrained(MODEL_ID)
    model = ORTModelForFeatureExtraction.from_pretrained(ONNX_MODEL_PATH)
    print(f"✅ Modelo ONNX e Tokenizer carregados com sucesso de: {ONNX_MODEL_PATH}")
except Exception as e:
    print(f"❌ Erro ao carregar o modelo ou tokenizer: {e}")
    print(f"⚠️ Rode: python quantize_model.py")
    raise RuntimeError(f"Falha ao inicializar a aplicação: {e}")

app = FastAPI(
    title=f"Embedding API - {model_name} Quantized ONNX",
    description=f"API para gerar embeddings de texto usando o modelo {model_name} (ONNX quantizado)."
)

# Função para criar input instruct
def get_detailed_instruct(task_description: str, query: str) -> str:
    return f'Instruct: {task_description}\nQuery: {query}'

# Modelos Pydantic
class TextListRequest(BaseModel):
    texts: List[str]
    use_instruct: Optional[bool] = False
    task_description: Optional[str] = "Given a query, retrieve relevant documents"

class TextRequest(BaseModel):
    text: str
    use_instruct: Optional[bool] = False
    task_description: Optional[str] = "Given a query, retrieve relevant documents"

# Função de embedding
def generate_embedding(text: str) -> List[float]:
    inputs = tokenizer(text, return_tensors="np", padding=True, truncation=True)
    outputs = model(**inputs)
    embedding = outputs.last_hidden_state.mean(axis=1)[0]
    norm = np.linalg.norm(embedding)
    if norm > 0:
        embedding = embedding / norm
    return embedding.tolist()

@app.post(
    "/embed",
    summary="Gera embeddings para armazenamento",
    response_description="Lista de embeddings gerados e suas dimensões",
    status_code=status.HTTP_200_OK
)
def embed_texts(request: TextListRequest):
    if not request.texts:
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="A lista de textos não pode estar vazia."
        )

    embeddings = []
    for text in request.texts:
        if not text or not text.strip():
            raise HTTPException(
                status_code=status.HTTP_400_BAD_REQUEST,
                detail="Cada texto deve ser não vazio."
            )

        # Se for "Instruct Mode", formata
        if request.use_instruct:
            text = get_detailed_instruct(request.task_description, text)

        embedding = generate_embedding(text)
        embeddings.append({
            "embedding": embedding,
            "dimension": len(embedding)
        })

    return {"results": embeddings}

@app.post(
    "/embed_query",
    summary="Gera embedding de query para busca vetorial",
    response_description="Embedding da query e sua dimensão",
    status_code=status.HTTP_200_OK
)
def embed_query(request: TextRequest):
    if not request.text or not request.text.strip():
        raise HTTPException(
            status_code=status.HTTP_400_BAD_REQUEST,
            detail="O texto da query não pode ser vazio ou conter apenas espaços."
        )

    text = request.text
    if request.use_instruct:
        text = get_detailed_instruct(request.task_description, text)

    embedding = generate_embedding(text)

    return {
        "embedding": embedding,
        "dimension": len(embedding)
    }
