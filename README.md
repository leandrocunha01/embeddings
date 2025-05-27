# Embedding API - multilingual-e5-small Quantized ONNX

API simples para geração de embeddings de texto usando o modelo `multilingual-e5-small`, convertido para ONNX e quantizado para melhor desempenho.

---

## Descrição

Este projeto oferece uma API REST construída com FastAPI para gerar embeddings de texto com o modelo `multilingual-e5-small` da Hugging Face, otimizado com ONNX Runtime. O modelo é convertido para ONNX e posteriormente quantizado para reduzir o tamanho e acelerar a inferência, mantendo boa precisão.

---

## Funcionalidades

- Conversão do modelo `multilingual-e5-small` para ONNX.
- Quantização do modelo ONNX para melhoria de desempenho.
- API para geração de embeddings via requisição HTTP.
- Pooling mean aplicado na saída do modelo para vetor fixo.
- Aplicação simples em Laravel + Qdrant criando e salvando vetores.

---

## Estrutura do projeto

- `main.py`: Código da API FastAPI para geração de embeddings.
- `onnx/`: Diretório onde os modelos ONNX e quantizados são salvos.
- `README.md`: Esta documentação.

---

## Requisitos

- Python 3.2+
- pacotes:
  - fastapi
  - uvicorn
  - transformers
  - optimum
  - onnxruntime
  - numpy
  - pydantic

---

## Configurando ambiente com `venv`

Recomendo usar um ambiente virtual para isolar as dependências do projeto:

```bash
# Criar ambiente virtual
python3 -m venv venv

# Ativar ambiente virtual (Linux/macOS)
source venv/bin/activate

# Ativar ambiente virtual (Windows PowerShell)
.\venv\Scripts\Activate.ps1

# Ativar ambiente virtual (Windows CMD)
.\venv\Scripts\activate.bat

## Instalando depedências

pip install -r requirements.txt

## Rodando

uvicorn main:app --reload

## Chamada para gerar embedding

curl -X POST "http://localhost:8000/embed" -H "Content-Type: application/json" -d '{"text": "Teste de embeddisdang"}'

