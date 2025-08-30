FROM python:3.11-slim
EXPOSE 8020

WORKDIR /code

COPY requirements.txt /code/requirements.txt

RUN pip install --upgrade pip
RUN pip install -r requirements.txt

COPY ./ ./

ENTRYPOINT ["uvicorn", "main_simple:app", "--proxy-headers", "--host", "0.0.0.0", "--port", "8020"]
