import mysql.connector
import json
from datetime import datetime

def formatar_tempo(tempo_str):
    try:
        minutos, resto = tempo_str.strip().split(":")
        segundos, centesimos = resto.strip().split(" ")
        return f"{int(minutos):02d}:{int(segundos):02d}.{int(centesimos):02d}"
    except Exception as e:
        print(f"Erro ao converter tempo: {tempo_str} - {e}")
        return None

def tempo_para_centesimos(tempo_str):
    try:
        parte1, centesimos = tempo_str.strip().split(' ')
        minutos, segundos = map(int, parte1.split(':'))
        return (minutos * 60 + segundos) * 100 + int(centesimos)
    except ValueError:
        return None  # ou lance um erro dependendo da sua necessidade

def salvar_em_mysql(json_path):
    conn = mysql.connector.connect(
        host="mysql.l3swim.com.br",
        user="l3swim",
        password="natacao09",
       database="l3swim"
    )
    #conn = mysql.connector.connect(
    #    host="localhost",
    #    user="root",
    #    password="",
    #    database="natacao"
    #)
    cursor = conn.cursor()

    with open(json_path, "r", encoding="utf-8") as f:
        data = json.load(f)

    campeonato_raw = data["campeonato"]
    nome_2 = campeonato_raw[1].strip()
    local = campeonato_raw[2].strip()
    piscina = campeonato_raw[3].strip()
    datas = campeonato_raw[4].strip()
    data_final = datas.split("à")[1].strip()
    data_final_obj = datetime.strptime(data_final, "%d/%m/%Y")
    ano_final = data_final_obj.year

    cursor.execute("INSERT INTO campeonato (nome, cidade, piscina, realizacao, ano) VALUES (%s, %s, %s, %s, %s)", 
                   (nome_2, local, piscina, datas, ano_final,))
    id_camp = cursor.lastrowid

    for prova in data["provas"]:
        dataCamp = datetime.strptime(prova.get("data", None), "%d/%m/%Y").strftime("%Y-%m-%d")
        descricao = prova.get("descricao", "")
        partesProva = descricao.split()
        numero = partesProva[0]
        primeira_letra = partesProva[2][0]
        resultadoProva = numero + primeira_letra

        cursor.execute("""
            INSERT INTO prova (campeonato_id, prova, descricao, categoria, data)
            VALUES (%s, %s, %s, %s, %s)
        """, (
            id_camp,
            prova.get("prova", ""),
            descricao,
            prova.get("categoria", ""),
            dataCamp
        ))
        id_prova = cursor.lastrowid

        for resultado in prova["resultados"]:
            if "linha_bruta" in resultado:
                cursor.execute("""
                    INSERT INTO linha_nao_reconhecida (prova_id, texto)
                    VALUES (%s, %s)
                """, (id_prova, resultado["linha_bruta"]))
            else:
                # Verificar ou inserir atleta
                registro = resultado.get("registro")
                atleta = resultado.get("atleta")
                nascimento = resultado.get("nascimento")

                cursor.execute("SELECT id FROM atleta WHERE registro = %s", (registro,))
                atleta_row = cursor.fetchone()
                if atleta_row:
                    atleta_id = atleta_row[0]
                else:
                    cursor.execute("INSERT INTO atleta (registro, nome, nascimento) VALUES (%s, %s, %s)",
                                   (registro, atleta, nascimento))
                    atleta_id = cursor.lastrowid

                # Tempo
                tempo_bruto = resultado.get("tempo")
                tempo_centesimos = tempo_para_centesimos(tempo_bruto)
                tempo_formatado = formatar_tempo(tempo_bruto)

                cursor.execute("""
                    INSERT INTO resultado (
                        prova_id, atleta_id, colocacao, serie, raia, atleta, registro,
                        nascimento, entidade, tempo, tempo_centesimos, pontos, indice, sexo, prova
                    ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
                """, (
                    id_prova,
                    atleta_id,
                    resultado.get("colocacao"),
                    resultado.get("serie"),
                    resultado.get("raia"),
                    atleta,
                    registro,
                    nascimento,
                    resultado.get("entidade"),
                    tempo_formatado,
                    tempo_centesimos,
                    resultado.get("pontos"),
                    resultado.get("indice"),
                    descricao.split()[-1],
                    resultadoProva
                ))

    conn.commit()
    cursor.close()
    conn.close()
    print("✅ Dados salvos no MySQL!")

# Uso
salvar_em_mysql("carregados/resultado_formatado_tomada_tempo_iate_2025.json")