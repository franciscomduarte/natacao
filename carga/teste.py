import pdfplumber
import re
import json

def extrair_data_formatada(linha):
    match = re.search(r'(\d{1,2})/(\d{1,2})/(\d{4})\)', linha)
    if match:
        dia = match.group(1).zfill(2)  # adiciona 0 à esquerda no dia
        mes = match.group(2).zfill(2)  # adiciona 0 à esquerda no mês
        ano = match.group(3)
        return f"{dia}/{mes}/{ano}"
    return None
def extrair_data(linha):
    match = re.search(r'\((\d{1,2}/\d{1,2}/\d{4})\)', linha)
    if match:
        return match.group(1)
    return None

def remover_sigla_parenteses(texto):
    texto_normalizado = re.sub(r'\s{2,}', ' ', texto)
    # Remove padrões como (JR), (abc), (X), com ou sem espaço antes
    return re.sub(r'\(\s*[A-Za-z.]{0,4}\s*\)?', '', texto_normalizado).strip()

def limpar_linhas(linhas):
    ignorar_inicios = ['RCO ', 'RM ', 'RMJ ', 'RS ', 'RB ', 'RT:0', '100M:', 'RBC ', 'RC ', 'Final A', 'Final B', 'DQL ', 'CVD ', 'N/C ', 'REU ', 'ABSD', 'CATD','Col', 'OBS']
    ignorar_finais = [' 0,00 0 E', ' 0,00 E']
    ignorar_exatas = [
        "Software de Apuração Oficial de Natação CBDA Vida Atleta de Vantagens",
        "SGE - Maior plataforma de Gestão Esportiva do país Escaneie o qr code e cadastre-se",
        "Piscina - Versão: 3.12.0.0 15/04/2025 13:24:23 q4rlTotal",
        "Tempo",
        "Col. S R Nome Patrocínio Reg. C. Nasc. Entidade Pts. IT",
        "Obtido",
        "Resultados da Final",
        "Software de Apuração Oficial Natação CBDA Vida Atleta de Vantagens"
    ]

    linhas_limpa = []
    skip_proxima = False

    for linha in linhas:
        linha = linha.strip()

        if any(linha.startswith(inicio) for inicio in ignorar_inicios):
            continue
            
        if any(linha.endswith(final) for final in ignorar_finais):
            continue

        if linha in ignorar_exatas:
            continue

        if skip_proxima:
            skip_proxima = False
            continue

        if linha.endswith("Total"):
            continue

        # Remove linhas como "2300M: parcial" ou "100M: tempo"
        if re.match(r"^\d{2,4}M:", linha):
            continue

        # Remove linhas que começam com 6 dígitos seguidos de nome em maiúsculas
        if re.match(r"^\d{6}\s+[A-ZÀ-Ú]+\s+[A-ZÀ-Ú]+", linha):
            continue

        if linha.startswith("RT:"):
            continue

        linha = linha.replace("Piscina - Versão: ", "")

        linha = remover_sigla_parenteses(linha)

        linhas_limpa.append(linha)

    return linhas_limpa

def extrair_provas(paginas, linhas_cabecalho):
    provas = []
    prova_atual = None

    for pagina in paginas:
        linhas = pagina["linhas"]
        for i, linha in enumerate(linhas):
            if linha in linhas_cabecalho:
                continue

            if re.match(r"^\d+ª?\s+PROVA", linha):
                linha = re.sub(r"(\d+ª?\s*PROVA)(\S)", r"\1 \2", linha)
                
                # Ignora provas que contenham "REV"
                if re.search(r"PROVA.*REV", linha.upper()):
                    prova_atual = None
                    continue

                if prova_atual:
                    provas.append(prova_atual)

                match = re.match(r"^(\d+ª?\s*PROVA)\s+(.*?)\s+-\s+(.*?)\s+(\d{2}/\d{2}/\d{4})$", linha.strip())

                if match:
                    prova_label = match.group(1).strip()
                    descricao = match.group(2).strip()
                    categoria = match.group(3).strip()
                    data = match.group(4).strip()
                else:
                    prova_label = ""
                    descricao = linha.strip()
                    categoria = ""
                    data = ""

                prova_atual = {
                    "prova": prova_label,
                    "descricao": descricao,
                    "categoria": categoria,
                    "data": data,
                    "resultados": []
                }
            elif re.match(r"^Prova\s+N[°ºoO]\s*\d+\s+Resultado\s+da\s+Final", linha, re.IGNORECASE):
                if prova_atual:
                    provas.append(prova_atual)

                numero_match = re.search(r"N[°ºoO]\s*(\d+)", linha)
                numero = numero_match.group(1) if numero_match else ""
                prova_label = f"{numero}ª PROVA"

                # Pegando linha anterior e próximas duas linhas
                descricao = ""
                categoria = ""
                data = ""

                if i > 0:
                    descricao = linhas[i-1].strip()
                if i+1 < len(linhas):
                    categoria = linhas[i+1].strip()
                if i+2 < len(linhas):
                    data = linhas[i+2].strip()

                data = extrair_data_formatada(data)

                prova_atual = {
                    "prova": prova_label,
                    "descricao": descricao,
                    "categoria": categoria,
                    "data": data,
                    "resultados": []
                }
                continue

            else:
                if prova_atual:
                    linha = re.sub(r"[-*]", " ", linha)
                    linha = re.sub(r"\s{2,}", " ", linha)
                    linha = re.sub(r"[.]", " ", linha)
                    linha = re.sub(r"(?<=[A-Z\-])(\d{2}[:]\d{2}[\.\d]*)", r" \1", linha.strip())
                    linha = re.sub(r"([A-Z]{2,})(\d{2}[:.]\d{2}(?:[:.]\d{2})?)", r"\1 \2", linha)

                    match = re.match(
                        r"^(\d+[°ºoO]?)\s+(\d+)\s+(\d+)\s+([a-zà-úA-ZÀ-Ú0-9\s]+(?:\s*\([a-zA-Z]{1,4}\))?)\s+([A-Z]?\d{5,6})\s+(?:\s*\([A-Za-z]{1,4}\))?\s?+(\d{1,4})\s+([A-ZÀ-Ú0-9º\/\s]+?)\s*(?=\d{2}[: ]\d{2}[\. ]\d{2})([\d:.\s]+)\s+([\d,]+)\s+(\d+)",
                        linha
                    )

                    if match:
                        print(linha)
                        resultado = {
                            "colocacao": match.group(1),
                            "serie": match.group(2),
                            "raia": match.group(3),
                            "atleta": match.group(4).strip(),
                            "registro": match.group(5),
                            "nascimento": match.group(6),
                            "entidade": match.group(7).strip(),
                            "tempo": match.group(8).strip(),
                            "pontos": match.group(9),
                            "indice": match.group(10),
                        }
                        prova_atual["resultados"].append(resultado)
                    else:
                        prova_atual["resultados"].append({"linha_bruta": linha})

    if prova_atual:
        provas.append(prova_atual)

    return provas

def pdf_para_json(path_pdf):
    with pdfplumber.open(path_pdf) as pdf:
        paginas = []
        campeonato = []

        for idx, page in enumerate(pdf.pages, start=1):
            linhas = page.extract_text().split('\n')
            linhas = limpar_linhas(linhas)

            if idx == 1:
                campeonato = linhas[:5]
                linhas = linhas[5:]

            paginas.append({
                "pagina": idx,
                "linhas": linhas
            })

        provas = extrair_provas(paginas, campeonato)

        return {
            "campeonato": campeonato,
            "provas": provas
        }

# Uso
if __name__ == "__main__":
    nome_arquivo = "Torneio-Abertura-2025"
    caminho_pdf = nome_arquivo + ".pdf"
    resultado_json = pdf_para_json(caminho_pdf)

    with open("resultado_formatado_" + nome_arquivo + ".json", "w", encoding="utf-8") as f:
        json.dump(resultado_json, f, ensure_ascii=False, indent=2)

    print("✅ Conversão finalizada com sucesso!")
