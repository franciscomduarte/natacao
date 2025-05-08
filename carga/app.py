import streamlit as st
import pdfplumber
import os
import re
import pandas as pd
import mysql.connector
from mysql.connector import Error
from sqlalchemy import create_engine
import mysql.connector
from datetime import datetime
from io import BytesIO
import docling
from docling import Document
import json

# Função para extrair o ano da data
def pegar_ano_primeira_data(texto):
    match = re.search(r"\d{2}/\d{2}/(\d{4})", texto)
    if match:
        return int(match.group(1))
    return 0

# Função para criar conexão com o banco
def conectar_mysql():
    return create_engine("mysql+mysqlconnector://root:@localhost/natacao")  # atualize com seus dados

# Função para quebrar string da prova
def quebrar_info_prova(linha):
    padrao = r"^(\d+ª?\s*PROVA)\s*(\d+\s+METROS)\s+([A-ZÀ-Ú\s]+?)\s{2,}([A-ZÀ-Ú]+\s*\d*)\s+(\d{2}\s\d{2}\s\d{4})$"
    match = re.match(padrao, linha.strip())
    if match:
        prova, distancia, estilo_genero, categoria, data = match.groups()
        return (
            prova.strip(),
            distancia.strip(),
            estilo_genero.strip(),
            categoria.strip(),
            data.strip()
        )
    return ("Desconhecida", "Desconhecida", "Desconhecida", "Desconhecida", "Desconhecida")

# Função principal de extração
def extrair_resultados(pdf_path):
    resultados = []
    prova_atual = "Desconhecida"
    campeonato_info = "Desconhecido"
    cabecalho = []
    ano = 0

    with pdfplumber.open(pdf_path) as pdf:
        for page in pdf.pages:
            text = page.extract_text()
            if text:
                linhas = text.split("\n")

                # Filtrando linhas indesejadas
                linhas = [linha for linha in linhas if all(x not in linha for x in [
                    "Software de Apuração Oficial de Natação",
                    "CBDA Vida Atleta de Vantagens",
                    "SGE - Maior plataforma de Gestão Esportiva",
                    "Piscina - Versão:"
                ])]

                if not cabecalho and len(linhas) >= 5:
                    cabecalho = linhas[:5]
                    ano = pegar_ano_primeira_data(cabecalho[4])

                if "TORNEIO" in linhas[0] or "CAMPEONATO" in linhas[0]:
                    campeonato_info = linhas[1].strip()

                for linha in linhas:
                    linha = re.sub(r"[-/*]", " ", linha)
                    if "PROVA" in linha:
                        prova_atual = linha.strip()
                    else:
                        linha = re.sub(r"\s{2,}", " ", linha)
                        linha = re.sub(r"[.]", " ", linha)
                        
                    pattern = r"(\d+º)\s+(\d+)\s+(\d+)\s+([A-ZÀ-Ú0-9\s]+?)\s+(\d{5,6})\s+(\d{4})\s+([A-ZÀ-Ú0-9\s]+?)\s*(?=[0-9]{2}[: ][0-9]{2}[\. ][0-9]{2})([\d:.\s]+)\s+([\d,]+)\s+(\d+)"
                    match = re.match(pattern, linha.strip())

                    if match:
                        dados_prova = quebrar_info_prova(prova_atual)

                        resultados.append({
                            "Campeonato": campeonato_info,
                            "Prova": dados_prova[0],
                            "Distância": dados_prova[1],
                            "Estilo": dados_prova[2],
                            "Categoria": dados_prova[3],
                            "Data": dados_prova[4],
                            "Colocação": match.group(1),
                            "Série": match.group(2),
                            "Raia": match.group(3),
                            "Atleta": match.group(4).strip(),
                            "Registro": match.group(5),
                            "Nascimento": match.group(6),
                            "Entidade": match.group(7).strip(),
                            "Tempo": match.group(8),
                            "Pontos": match.group(9),
                            "ID Técnico": match.group(10),
                            "Ano": ano
                        })
    return resultados

def salvar_no_mysql(dados, host, user, password, database):
    try:
        conn = mysql.connector.connect(
            host=host,
            user=user,
            password=password,
            database=database
        )
        cursor = conn.cursor()

        insert_query = """
            INSERT INTO resultados_natacao (
                campeonato, prova, distancia, estilo, categoria, data_prova,
                colocacao, serie, raia, atleta, registro, nascimento, entidade,
                tempo, pontos, id_tecnico, ano
            ) VALUES (
                %s, %s, %s, %s, %s, %s,
                %s, %s, %s, %s, %s, %s, %s,
                %s, %s, %s, %s
            )
        """

        for row in dados:
            cursor.execute(insert_query, (
                row["Campeonato"], row["Prova"], row["Distância"], row["Estilo"],
                row["Categoria"], row["Data"], row["Colocação"], row["Série"],
                row["Raia"], row["Atleta"], row["Registro"], row["Nascimento"],
                row["Entidade"], row["Tempo"], row["Pontos"], row["ID Técnico"],
                row["Ano"]
            ))

        conn.commit()
        cursor.close()
        conn.close()
        st.success("✅ Dados salvos no banco de dados com sucesso!")

    except Error as e:
        st.error(f"❌ Erro ao salvar no banco: {e}")

def carregar_dados_banco():
    engine = conectar_mysql()
    query = "SELECT * FROM resultados_natacao"
    return pd.read_sql(query, engine)


# --- STREAMLIT APP ---

st.set_page_config(page_title="Análise de Resultados de Natação", page_icon="🏊", layout="wide")

st.title("🏊 Análise de Resultados de Campeonatos de Natação")

# Upload de arquivo PDF
pdf_file = st.file_uploader("Escolha um arquivo PDF com resultados de natação", type="pdf")

if pdf_file:
    with st.spinner("🔍 Lendo e processando o PDF..."):
        with open("temp.pdf", "wb") as f:
            f.write(pdf_file.read())
        dados = extrair_resultados("temp.pdf")

    if dados:
        df = pd.DataFrame(dados)

        st.success("✅ Resultados extraídos com sucesso!")

        st.subheader("🔍 Filtros")

        col1, col2, col3 = st.columns(3)
        col4, col5, col6 = st.columns(3)

        with col1:
            filtro_prova = st.selectbox("Prova", ["Todas"] + sorted(df["Prova"].unique()))
        with col2:
            filtro_distancia = st.selectbox("Distância", ["Todas"] + sorted(df["Distância"].unique()))
        with col3:
            filtro_estilo = st.selectbox("Estilo", ["Todas"] + sorted(df["Estilo"].unique()))
        with col4:
            filtro_categoria = st.selectbox("Categoria", ["Todas"] + sorted(df["Categoria"].unique()))
        with col5:
            filtro_atleta = st.selectbox("Atleta", ["Todos"] + sorted(df["Atleta"].unique()))
        with col6:
            filtro_entidade = st.selectbox("Entidade", ["Todas"] + sorted(df["Entidade"].unique()))

        # Aplicando os filtros
        if filtro_prova != "Todas":
            df = df[df["Prova"] == filtro_prova]
        if filtro_distancia != "Todas":
            df = df[df["Distância"] == filtro_distancia]
        if filtro_estilo != "Todas":
            df = df[df["Estilo"] == filtro_estilo]
        if filtro_categoria != "Todas":
            df = df[df["Categoria"] == filtro_categoria]
        if filtro_atleta != "Todos":
            df = df[df["Atleta"] == filtro_atleta]
        if filtro_entidade != "Todas":
            df = df[df["Entidade"] == filtro_entidade]

        st.subheader("📊 Tabela de Resultados Filtrados")
        st.dataframe(df, use_container_width=True)
    else:
        st.warning("⚠️ Nenhum resultado encontrado no PDF.")

        st.subheader("💾 Salvar no Banco de Dados")

    if st.button("Salvar Resultados no MySQL"):
        salvar_no_mysql(dados, "localhost", "root", "", "natacao")

        st.subheader("📤 Exportar ou Visualizar Dados do Banco")
else:
    st.info("📂 Faça upload de um arquivo PDF para iniciar a análise.")


if st.button("📥 Carregar dados do banco"):
    df_banco = carregar_dados_banco()
    st.dataframe(df_banco, use_container_width=True)

    csv = df_banco.to_csv(index=False).encode("utf-8")
    # Converter para Excel usando BytesIO
    excel_buffer = BytesIO()
    df_banco.to_excel(excel_buffer, index=False, engine='openpyxl')
    excel_buffer.seek(0)  # Volta para o início do arquivo

    st.download_button("📄 Baixar CSV", data=csv, file_name="resultados.csv", mime="text/csv")
    st.download_button(
        label="📊 Baixar Excel",
        data=excel_buffer,
        file_name="resultados.xlsx",
        mime="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    )