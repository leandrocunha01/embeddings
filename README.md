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

---

## Estrutura do projeto

- `main.py`: Código da API FastAPI para geração de embeddings.
- `onnx/`: Diretório onde os modelos ONNX e quantizados são salvos.
- `README.md`: Esta documentação.

---

## Requisitos

- Python 3.8+
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

Recomendamos usar um ambiente virtual para isolar as dependências do projeto:

```bash
# Criar ambiente virtual
python3 -m venv venv

# Ativar ambiente virtual (Linux/macOS)
source venv/bin/activate

# Ativar ambiente virtual (Windows PowerShell)
.\venv\Scripts\Activate.ps1

# Ativar ambiente virtual (Windows CMD)
.\venv\Scripts\activate.bat
