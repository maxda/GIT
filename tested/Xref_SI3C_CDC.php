<?php
function Xref_SI3C_CDC($centro_di_costo){
    $_Xref_SI3C_CDC=array(
            'CARCHI'=>801 , // CARDIOCHIRURGIA --> SOC Cardiochirurgia
            '000'=>1706 , // DEPOSITO PATRIMONIALE --> SOC Aprovvigionamenti e Logistica
            'AFFGEN'=>1704 , // SOC AFFARI GENERALI --> DPT Amministrativo
            'IRREP'=>1704 , // APPARECCHIATURE COMMESSE PRECEDENTI IRREPERIBILI --> DPT Amministrativo
            'FARMA'=>1102 , // SOC FARMACIA --> SOC Farmacia
            'GASTRO'=>902 , // GASTROENTEROLOGIA --> SOC Gastroenterologia
            'CARDIO'=>800 , // CARDIOLOGIA --> SOC Cardiologia
            'DIRSAN'=>1101 , // DIREZIONE SANITARIA --> DPT Organizzazione dei Servizi Ospedalieri
            'PMUNEMAT'=>22 , // PROG.MID.POL.UNIV.EMATOLOGIA --> SOC Clinica Ematologica
            'X-CIVDYS'=>1687 , // PA-CIVIDALE COMPARTO OPERATORIO --> SOC Direzione Medica di Presidio
            'X-CIVPS'=>1687 , // PA-CIVIDALE PRONTO SOCCORSO --> SOC Direzione Medica di Presidio
            'X-CIVRX'=>38 , // PA-RADIOLOGIA CIVIDALE --> SOS Radiodiagnostica dUrgenza ed Emergenza
            'X-DHAMB'=>1708 , // PA-D.H. ED AMBULATORIO MEDICHE --> UO Day Hospital
            'X-DIAB'=>502 , // PA-DIABETOLOGIA --> SOC Endocrinologia e Malattie del Metabolismo
            'X-PENS'=>1687 , // PA-PENSIONANTI --> SOC Direzione Medica di Presidio
            'X-PUGD'=>1687 , // PA-POLICLINICO UNIVERSITARIO --> SOC Direzione Medica di Presidio
            'X-RMN'=>1003 , // PA-RISONANZA MAGNETICA --> SOC Istituto di Radiologia Diagnostica
            'X-SANCIV'=>1687 , // PA-AREA SANITARIA CIVIDALE --> SOC Direzione Medica di Presidio
            'X-UNLAB'=>33 , // PA-ISTITUTO LABORATORIO ANALISI --> SOC Istituto di Patologia Clinica
            'DP_AMMI'=>1704 , // DIPARTIMENTO AMMINISTRATIVO --> DPT Amministrativo
            'DP_CATO'=>8 , // DIPARTIMENTO CARDIOTORACICO --> DPT Dipartimento Cardiotoracico
            'DP_ANES'=>1 , // DIPARTIMENTO DI ANESTESIA E RIANIMAZIONE --> DPT Anestesia e Rianimazione
            'DP_VAST'=>6 , // DIPARTIMENTO DI AREA VASTA DI MEDICINA TRASFUSIONALE --> DPT Area Vasta di Medicina Trasfusionale
            'DP_CHGE'=>9 , // DIPARTIMENTO DI CHIRURGIA GENERALE --> DPT Chirurgia Generale
            'DP_CHSP'=>2 , // DIPARTIMENTO DI CHIRURGIA SPECIALISTICA --> DPT Chirurgia Specialistica
            'DP_DIIM'=>10 , // DIPARTIMENTO DI DIAGNOSTICA PER IMMAGINI --> DPT Diagnostica per Immagini
            'DP_MLAB'=>3 , // DIPARTIMENTO DI MEDICINA DI LABORATORIO --> DPT Medicina di Laboratorio
            'DP_MINT'=>5 , // DIPARTIMENTO DI MEDICINA INTERNA --> DPT Medicina Interna
            'DP_MSPE'=>4 , // DIPARTIMENTO DI MEDICINA SPECIALISTICA --> DPT Medicina Specialistica
            'DP_NEUR'=>16 , // DIPARTIMENTO DI NEUROSCIENZE --> DPT Neuroscienze
            'DP_ONCO'=>14 , // DIPARTIMENTO DI ONCOLOGIA (*) --> DPT Oncologia
            'DP_OSOP'=>1101 , // DIPARTIMENTO DI ORGANIZZAZIONE DEI SERVIZI OSPEDALIERI --> DPT Organizzazione dei Servizi Ospedalieri
            'DP_MAIN'=>13 , // DIPARTIMENTO MATERNO-INFANTILE --> DPT Dipartimento Materno-infantile
            'DP_TECN'=>11 , // DIPARTIMENTO TECNICO --> DPT Dipartimento Tecnico
            'ANE_TI'=>17 , // ANESTESIA E TERAPIA INTENSIVA --> SOC Clinica di Anestesia e Rianimazione
            'ANES_1'=>100 , // ANESTESIA E RIANIMAZIONE (1) --> SOC Anestesia e Rianimazione (1)
            'ANES_2'=>100 , // ANESTESIA E RIANIMAZIONE (2) --> SOC Anestesia e Rianimazione (1)
            'ANTALG'=>102 , // SOS DI DPT TERAPIA ANTALGICA E ANESTESIA DAY SURGERY --> SOS Terapia Antalgica e Anestesia Day Surgery
            'MAXIL_1'=>200 , // CHIRURGIA MAXILLO-FACCIALE (1) --> SOC Chirurgia Maxillo-facciale
            'MAXIL_2'=>19 , // CHIRURGIA MAXILLO-FACCIALE (2) --> SOC Clinica di Chirurgia Maxillo-facciale
            'PLAST_1'=>201 , // CHIRURGIA PLASTICA (1) --> SOC Chirurgia Plastica
            'PLAST_2'=>20 , // CHIRURGIA PLASTICA (2) --> SOC Clinica di Chirurgia Plastica
            'OCUL_1'=>203 , // OCULISTICA (1) --> SOC Oculistica
            'OCUL_2'=>25 , // OCULISTICA (2) --> SOC Clinica di Oculistica
            'ORL_1'=>204 , // ORL (1) --> SOC Otorinolaringoiatria
            'ORL_2'=>28 , // ORL (2) --> SOC Clinica Otorinolaringoiatrica
            'ANPAT_1'=>300 , // ANATOMIA PATOLOGICA (1) --> SOC Anatomia Patologica
            'ANPAT_2'=>300 , // ANATOMIA PATOLOGICA (2) --> SOC Anatomia Patologica
            'GENET'=>36 , // GENETICA --> SOC Istituto di Genetica Medica
            'LAB_ELE'=>301 , // PA-LABORATORIO ANALISI - ELEZIONE --> SOC Laboratorio Analisi d'Elezione
            'LAB_URG'=>302 , // PA-LABORATORIO ANALISI - URGENZA --> SOC Laboratorio dUrgenza e Cividale
            'MAL_RAR'=>60 , // MALATTIE RARE --> SOC Centro di Coordinamento Regionale Malattie Rare
            'MICROB'=>303 , // MICROBIOLOGIA --> SOC Microbiologia
            'PAT_CLI'=>33 , // PATOLOGIA CLINICA --> SOC Istituto di Patologia Clinica
            'ALLERG'=>304 , // SOS DI DPT IMMUNOLOGIA E ALLERGOLOGIA --> SOS  Immunopatologia e Allergologia Diagnostica
            'EN_CRIN'=>502 , // ENDOCRINOLOGIA E MALATTIE DEL METABOLISMO --> SOC Endocrinologia e Malattie del Metabolismo
            'FAR_CO'=>35 , // FARMACOLOGIA --> SOC Istituto di Farmacologia Clinica
            'MED_INT1'=>503 , // MEDICINA INTERNA (1) --> SOC Medicina Interna (1)
            'MED_INT2'=>504 , // MEDICINA INTERNA (2) --> SOC Medicina Interna (2)
            'MED_INT3'=>24 , // MEDICINA INTERNA (3) --> SOC Clinica Medica
            'PRO_SOC'=>505 , // PRONTO SOCCORSO E MEDICINA D'URGENZA --> SOC Pronto Soccorso e Medicina dUrgenza
            'PSICH'=>30 , // PSICHIATRIA --> SOC Clinica Psichiatria
            'MED_CIV'=>509 , // SOS DI DPT MEDICINA INTERNA CIVIDALE --> SOS Medicina Interna Cividale
            'TRAT_BAS'=>506 , // SOS DI DPT TRATTAMENTO DEL PAZIENTE A BASSA INTENSITA DI CUR --> SOS Post Acuti
            'DERM_1'=>500 , // DERMATOLOGIA (1) --> SOC Dermatologia
            'DERM_2'=>21 , // DERMATOLOGIA (2) --> SOC Clinica Dermatologica
            'EMATO'=>22 , // EMATOLOGIA (*) --> SOC Clinica Ematologica
            'MAL_INF'=>23 , // MALATTIE INFETTIVE --> SOC Clinica di Malattie Infettive
            'DIALI'=>507 , // NEFROLOGIA, DIALISI E TRAPIANTO RENALE --> SOC Nefrologia, Dialisi e Trapianto Renale
            'REUMA'=>31 , // REUMATOLOGIA --> SOC Clinica di Reumatologia
            'NUTRIZ'=>508 , // SOS DI DPT NUTRIZIONE CLINICA --> SOS Nutrizione Clinica
            'TRASF'=>600 , // MEDICINA TRASFUSIONALE DI UDINE --> SOS Medicina Trasfusionale di Udine
            'TRASF_P'=>601 , // MEDICINA TRASFUSIONALE PALMANOVA --> SOC Medicina Trasfusionale Palmanova
            'TRASF_D'=>603 , // SOS DI DPT MEDICINA TRASFUSIONALE SAN DANIELE --> SOS Medicina Trasfusionale San Daniele
            'TRASF_T'=>602 , // SOS DI DPT MEDICINA TRASFUSIONALE TOLMEZZO --> SOS Medicina Trasfusionale Tolmezzo
            'CHITOR'=>1302 , // CHIRURGIA TORACICA --> SOC Chirurgia Toracica
            'PNEUM'=>802 , // PNEUMOLOGIA E FISIOPATOLOGIA RESPIRATORIA --> SOC Pneumologia e Fisiopatologia Respiratoria
            'CHIGEN1'=>900 , // CHIRURGIA GENERALE (1) --> SOC Chirurgia Generale
            'CHIGEN2'=>18 , // CHIRURGIA GENERALE (2) --> SOC Clinica Chirurgica
            'CHI_VAS'=>901 , // CHIRURGIA VASCOLARE --> SOC Chirurgia Vascolare
            'ORTO'=>903 , // ORTOPEDIA --> SOC Ortopedia e Traumatologia
            'TRAUM'=>26 , // ORTOPEDIA E TRAUMATOLOGIA --> SOC Clinica Ortopedica
            'DAYSUR'=>904 , // SOS DI DPT DAY SURGERY --> SOS Day Surgery
            'URO_1'=>904 , // UROLOGIA (1) --> SOS Day Surgery
            'URO_2'=>1501 , // UROLOGIA (2) --> SOC Clinica Urologica
            'RAD_IN'=>1004 , // DIAGNOSTICA ANGIOGRAFICA E RADIOLOGIA INTERVENTISTICA --> SOC Diagnostica Angiografica e Radiologia Interventistica
            'FISAN'=>1000 , // FISICA SANITARIA --> SOC Fisica Sanitaria
            'NUCLE'=>1001 , // MEDICINA NUCLEARE --> SOC Medicina Nucleare
            'NEURO'=>1002 , // NEURORADIOLOGIA --> SOC Neuroradiologia
            'RAD_DI'=>38 , // RADIOLOGIA DIAGNOSTICA --> SOS Radiodiagnostica dUrgenza ed Emergenza
            'RAD_UR'=>1003 , // SOS DI DPT RADIODIAGNOSTICA D'URGENZA ED EMERGENZA --> SOC Istituto di Radiologia Diagnostica
            'ING_CLIN'=>1707 , // INGEGNERIA CLINICA --> SOC Ingegneria Clinica
            'SER_TEC'=>1710 , // SERVIZI TECNICI --> SOC Servizi Tecnici
            'PATR'=>11 , // SOS DI DPT GESTIONE TECNICO AMMINISTRATIVA E PATRIMONIALE --> DPT Dipartimento Tecnico
            'INFOR'=>1709 , // TECNOLOGIA DELL'INFORMAZIONE E DELLA COMUNICAZIONE --> SOC Tecnologia dell'Informazione e Comunicazione
            'USNO'=>1711 , // UFFICIO SPECIALE NUOVO OSPEDALE --> SOC Grandi Opere
            'OSTE'=>27 , // OSTETRICIA E GINECOLOGIA --> SOC Clinica Ostetrica e Ginecologica
            'PATO_NE'=>1300 , // PATOLOGIA NEONATALE - NEONATOLOGIA --> SOC Patologia Neonatale
            'PADI'=>29 , // PEDIATRIA --> SOC Clinica Pediatrica
            'ONCO_1'=>1400 , // ONCOLOGIA (1) --> SOC Oncologia
            'ONCO_2'=>1400 , // ONCOLOGIA (2) --> SOC Oncologia
            'RAD_TE'=>1005 , // RADIOTERAPIA --> SOC Radioterapia
            'CH_VERT'=>202 , // CHIRURGIA VERTEBRO-MIDOLLARE --> SOC Chirurgia Vertebro-midollare e Unit� spinale
            'NUE_CHI'=>205 , // NEUROCHIRURGIA --> SOC Neurochirurgia
            'NEURO_1'=>1600 , // NEUROLOGIA (1) --> SOC Neurologia
            'NEURO_2'=>1600 , // NEUROLOGIA (2) --> SOC Neurologia
            'FISIOL_I'=>1686 , // SOS DI DPT NEUROFISIOLOGIA INTERVENTISTICA --> SOS Neurofisiologia Interventistica
            'APPLOG'=>1706 , // SOC APPROVVIGIONAMENTI E LOGISTICA --> SOC Aprovvigionamenti e Logistica
            'GPRP'=>1713 , // SOC GESTIONE DI PRESIDIO E RELAZIONI CON IL PUBBLICO --> SOS Ufficio Relazioni con il Pubblico
            'ECOFIN'=>1705 , // SOC GESTIONE ECONOMICO-FINANZIARIA --> SOC Ragioneria
            'RISUM'=>1712 , // SOC GESTIONE RISORSE UMANE --> SOC Risorse Umane
            'AFFLEG'=>1714 , // SOS DI DPT AFFARI LEGALI --> SOS Affari Legali
            'ACCRED'=>1688 , // SOC ACCREDITAMENTO --> SOC Accreditamento, Gestione del Rischio Clinico e Valutazione delle Performance Sanitarie
            'CO_118'=>1100 , // SOC CENTRALE OPERATIVA 118 ED ELISOCCORSO --> SOC Centrale Operativa 118 ed Elisoccorso
            'DPSAN'=>1690 , // SOC DIREZIONE DELLE PROFESSIONI SANITARIE --> SOC Direzione delle Professioni Sanitarie
            'DMP'=>1687 , // SOC DIREZIONE MEDICA DI PRESIDIO --> SOC Direzione Medica di Presidio
            'IGIENE'=>37 , // SOC IGIENE ED EPIDEMIOLOGIA CLINICA --> SOC Igiene ed Epidemiologia Clinica
            'UNSALOP'=>18 , // SALE OPERAT.MATERNO INFANT.UD --> SOC Clinica Chirurgica
            'UNSOPGE'=>1687 , // SALE OPERATORIE GEMONA --> SOC Direzione Medica di Presidio
            'X-118'=>1100 , // PA-118 --> SOC Centrale Operativa 118 ed Elisoccorso
            'FORM'=>1715 , // FORMAZIONE --> SOS Formazione
            'CR_TRAP'=>1687 , // CENTRO REGIONALE TRAPIANTI --> SOC Direzione Medica di Presidio
            'X-ACUTI'=>1687 , // PA-POST ACUTI - DPT MEDICO --> SOC Direzione Medica di Presidio
    );
    return $_Xref_SI3C_CDC[$centro_di_costo];   
}