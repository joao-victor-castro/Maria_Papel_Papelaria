let manuais = [];

// Filtra a cada alteracao nos comboboxes
const filtros_manuais = document.querySelectorAll(".filtros_manuais");

filtros_manuais.forEach(filtro =>{    
    filtro.addEventListener('change', async ()=>{        
        // Verifica se pelo menos uma combobox está preenchida
        const agrupamento = document.getElementById('filtroAgrupamento').value;
        if(!agrupamento){
            mostrarMsg("red", "Selecione um agrupamento", "erroFiltrar");
            return;
        }
        const ano_escolar = document.getElementById('filtroAnoEscolar').value;
        if(!ano_escolar){
            mostrarMsg("red", "Selecione um ano escolar", "erroFiltrar");
            return;
        }
        const tipo_manual = document.getElementById('filtroTipoManual').value;
    
        manuais = await filtrarManuais(agrupamento, ano_escolar, tipo_manual);
        renderTabela(manuais);
    })

})



// Selecionadores da tabela (campos selecionar e voucher)
const selecionarAll = document.getElementById('selecionarAll');
selecionarAll.addEventListener('change', function (){
    checkboxes = document.querySelectorAll('.checkbox-selecionar');
    checkboxes.forEach(checkbox=>{
        checkbox.checked = this.checked;
        
        if(this.checked == false){
            let id_manual = checkbox.dataset.id_manual;
            let checkVoucher = document.getElementById(`checkVoucher-${id_manual}`);
            if(checkVoucher){
                checkVoucher.checked = false;
            }
        }
    });
    
    atualizarTotal();
});

const voucherAll = document.getElementById('voucherAll');
voucherAll.addEventListener('change', function (){
    checkboxes = document.querySelectorAll('.checkbox-voucher');
    checkboxes.forEach(checkbox=>{
        checkbox.checked = this.checked;
        
        if(this.checked == true){
            let id_manual = checkbox.dataset.id_manual;
            document.getElementById(`checkSelecionar-${id_manual}`).checked = true;
        }
    });

    atualizarTotal();
});


// Modal de confirmar encomenda
const modalConfirmar = document.getElementById('modal-confirmar');
const closeModalConfirmar = document.getElementById('close-modal-confirmar');
closeModalConfirmar.addEventListener('click', ()=>{
    modalConfirmar.style.display = "none";
});

// Fecha o modal ao clicar fora da caixa do modal
window.addEventListener('click', function (e) {
    if (e.target === modalConfirmar) {
        modalConfirmar.style.display = 'none';
    }
});


// Mostra o modal de confirmar encomenda, e constrói o objeto a ser enviado pro backend
let encomenda = {};
const btnEncomendar = document.getElementById('btnEncomendar');
btnEncomendar.addEventListener('click', async function (){
    // Verificar se tem algum manual selecionado
    const manuais_selecionados = document.querySelectorAll('.checkbox-selecionar:checked');

    if(manuais_selecionados.length == 0){
        mostrarMsg("red", "Selecione pelo menos 1 manual");
        return;
    }

    const ano_escolar = document.getElementById('filtroAnoEscolar').value;
    encomenda.id_ano_escolar = ano_escolar;

    // Verificar se tem nome do aluno e do EE
    const nomeAluno = document.getElementById('nomeAluno').value;
    const nomeEnc = document.getElementById('nomeEnc').value;

    if(!nomeAluno){
        mostrarMsg("red", "Introduza o nome do aluno");
        return;
    }

    if(!nomeEnc){
        mostrarMsg("red", "Introduza o nome do encarregado de educação");
        return;
    }

    encomenda.nome_aluno_encomenda = nomeAluno;
    encomenda.nome_ee = nomeEnc;
    
    // Verificar se tem email ou telefone
    // Verificar se email tá certo
    const email = document.getElementById('email').value;
    const telemovel = document.getElementById('telemovel').value;

    if(!email && !telemovel){
        mostrarMsg("red", "Introduza pelo menos um email ou telemóvel");
        return;
    }

    if(email){
        if(/^[a-zA-Z0-9._%+\-]+@[a-zA-Z0-9.\-]+\.[a-zA-Z]{2,}$/.test(email.trim()) == false){
            mostrarMsg("red", "Introduza um email válido");
            return;
        }
    }

    encomenda.email_encomenda = email ?? null;
    encomenda.telemovel = telemovel ?? null;

    // Verificar se existe etiquetas: tem que ter observações
    const checkEtiquetas = document.getElementById('checkEtiquetas').checked;

    if(checkEtiquetas){
        const obs_etiquetas = document.getElementById('etiquetas').value;
        if(!obs_etiquetas){
            mostrarMsg("red", "Introduza o nome para a etiqueta");
            return;
        }

        encomenda.etiqueta = true;
        encomenda.obs_etiquetas = obs_etiquetas;
    }
    else{
        encomenda.etiqueta = false;
        encomenda.obs_etiquetas = null;
    }

    // Popular o modal com as informações

    const id_encomenda = await getIdEncomenda();

    const agrupamentos = document.getElementById('filtroAgrupamento');
    const agrupamento = agrupamentos.selectedOptions[0].text;
    document.getElementById('confirmar_agrupamento').innerText = agrupamento;
    encomenda.agrupamento = agrupamento;
    encomenda.id_agrupamento = agrupamentos.value;
    
    const anos = document.getElementById('filtroAnoEscolar');
    const ano = anos.selectedOptions[0].text;
    document.getElementById('confirmar_ano').innerText = ano;
    encomenda.ano = ano;

    renderTabelaConfirmar();

    const total_encomenda = document.getElementById('totalEncomenda').innerText;
    document.getElementById('confirmarTotalEncomenda').innerText = total_encomenda + "€";
    encomenda.total_encomenda = total_encomenda;

    const caucao_paga = document.getElementById('caucaoPaga').value;
    document.getElementById('confirmarCaucaoPaga').innerText = caucao_paga + "€";
    encomenda.caucao_paga = caucao_paga;
    
    const plastManuais = document.getElementById('plastificarManuais');
    if(plastManuais.checked){
        document.getElementById('confirmarPlastManuais').innerText = "Sim";
        encomenda.plast_manuais = true;
    }
    else{
        document.getElementById('confirmarPlastManuais').innerText = "Não";
        encomenda.plast_manuais = false;
    }

    const plastLivroFichas = document.getElementById('plastificarLivroDeFichas');
    if(plastLivroFichas.checked){
        document.getElementById('confirmarPlastLivroFichas').innerText = "Sim";
        encomenda.plast_livro_fichas = true;
    }
    else{
        document.getElementById('confirmarPlastLivroFichas').innerText = "Não";
        encomenda.plast_livro_fichas = false;
    }

    if(checkEtiquetas){
        document.getElementById('confirmarEtiquetas').innerText = "Sim - " + document.getElementById('etiquetas').value;
    }
    else{
        document.getElementById('confirmarEtiquetas').innerText = "Não";
    }

    const obs_encomenda = document.getElementById('observacoes').value;
    document.getElementById('confirmarObs').innerText = obs_encomenda;
    encomenda.obs_encomenda = obs_encomenda ?? null;

    const codigoMega = document.getElementById('codigoMega').value;
    document.getElementById('confirmarCodigoMega').innerText = codigoMega;
    encomenda.codigoMega = codigoMega;

    document.getElementById('confirmarAluno').innerText = document.getElementById('nomeAluno').value;

    const nif = document.getElementById('nif').value
    document.getElementById('confirmarNIF').innerText = nif;
    encomenda.nif = nif ?? "";
    document.getElementById('confirmarEnc').innerText = document.getElementById('nomeEnc').value;
    document.getElementById('confirmarEmail').innerText = document.getElementById('email').value;
    document.getElementById('confirmarTelemovel').innerText = document.getElementById('telemovel').value;


    modalConfirmar.style.display = "flex";
});


// Envia dados para serem adicionados à base de dados
const btnConfirmarEncomenda = document.getElementById('btnConfirmarEncomenda');
btnConfirmarEncomenda.addEventListener('click', async function(){
    const response = await fetch('encomendar_manuais.php', {
        method:"post",
        headers: { 'Content-Type': 'application/json' },
        body:JSON.stringify({
            "acao":"adicionar_encomenda",
            "encomenda":encomenda
        })
    });

    const data = await response.json()
    if(data["resultado"] == "sucesso"){
        document.getElementById('num_encomenda_sucesso').innerText = data["num_encomenda"];
        
        const link_pdf = document.getElementById('caminho_pdf_sucesso');
        link_pdf.innerText = data["caminho_pdf"];
        link_pdf.href = data["caminho_pdf"];

        document.getElementById('modal-sucesso').style.display = "flex";
        
        encomenda.num_encomenda = data["num_encomenda"];

        // TODO: Fetch silencioso pro envio do email
        fetch('enviar_email.php', {
            method:"post",
            headers:{'Content-Type':'application/json'},
            body: JSON.stringify({
                "tipo_email":"registada",
                "encomenda":encomenda,
                "caminho_pdf":data["caminho_pdf"]
            })
        });
    }
});

const btnCancelarEncomenda = document.getElementById('btnCancelarEncomenda');
btnCancelarEncomenda.addEventListener('click', ()=>{
    modalConfirmar.style.display = "none";
})


// Tabela confirmar
function renderTabelaConfirmar(){
    const tbody = document.getElementById('tabela-confirmar');
    tbody.innerHTML = '';
    encomenda.manuais = [];

    // Verifica cada linha da tabela de filtros e extrai somente os selecionados

    const tabelaFiltros = document.querySelectorAll('#tabela-filtro tr');

    tabelaFiltros.forEach(tr=>{
        const selecionado = tr.querySelector('.checkbox-selecionar');
        if(!selecionado.checked){
            return;
        }

        const linha = document.createElement('tr');
        const celulas = tr.querySelectorAll('td');

        
        let nome = document.createElement('td');
        nome.innerText = celulas[1].innerText;        
        linha.appendChild(nome);

        let disciplina = document.createElement('td');
        disciplina.innerText = celulas[3].innerText;        
        linha.appendChild(disciplina);

        let voucher = document.createElement('td');
        let checkvoucher = celulas[6].querySelector('input[type="checkbox"]');
        if(checkvoucher && checkvoucher.checked == true){
            voucher.innerText = "Sim";
        }    
        else{
            voucher.innerText = "Não";
        }
        linha.appendChild(voucher);
        tbody.appendChild(linha);

        encomenda.manuais.push({
            isbn: celulas[0].innerText,
            nome: celulas[1].innerText,
            preco: celulas[2].innerText,
            voucher: voucher.innerText,
            disciplina: celulas[3].innerText,
            tipo_manual: celulas[4].innerText,
            id_manual: celulas[0].dataset.id_manual
        });
    })

}


async function getIdEncomenda(){
    const id_ano_escolar = document.getElementById('filtroAnoEscolar').value;

    const response = await fetch('encomendar_manuais.php', {
        method:"post",
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            "acao":"get_id_encomenda",
            "id_ano_escolar": id_ano_escolar

        })
    });

    const data = await response.json();
    return data.resultado;    
}

// Função que calcula o valor total da encomenda
function atualizarTotal(){
    // Pega os valores de todos os campos "selecionar" marcados
    const checkboxSelecionar = document.querySelectorAll('.checkbox-selecionar:checked');
    let caucao = 0;
    let total = 0;

    checkboxSelecionar.forEach(manual=>{
        let id_manual = manual.dataset.id_manual;
        let voucher = document.getElementById(`checkVoucher-${id_manual}`);

        if(voucher && voucher.checked == true){
            return;
        }

        caucao += 5;
        total += parseFloat(manual.dataset.preco);
    })

    document.getElementById('totalEncomenda').innerText = total.toFixed(2);
    document.getElementById('valorCaucao').innerText = caucao;
}

// Função que constroi a tabela
function renderTabela(dados) {
    const tbody = document.getElementById('tabela-filtro');
    tbody.innerHTML = '';

    // Itera sobre cada linha e adiciona à tabela
    dados.forEach(element => {
        let linha = document.createElement('tr');

        let isbn_manual = document.createElement('td');
        isbn_manual.innerText = element.isbn_manual;
        isbn_manual.dataset.id_manual = element.id_manual;
        linha.appendChild(isbn_manual);

        let nome_manual = document.createElement('td');
        nome_manual.innerText = element.nome_manual;
        linha.appendChild(nome_manual);

        let preco_manual = document.createElement('td');
        preco_manual.innerText = element.preco_manual;
        linha.appendChild(preco_manual);

        let nome_disciplina = document.createElement('td');
        nome_disciplina.innerText = element.nome_disciplina;
        linha.appendChild(nome_disciplina);

        let tipo_manual = document.createElement('td');
        tipo_manual.innerText = element.tipo_manual;
        linha.appendChild(tipo_manual);

        // Campo selecionar
        let selecionar = document.createElement('td');

        let checkboxSelecionar = document.createElement('input');
        checkboxSelecionar.type = "checkbox";
        checkboxSelecionar.dataset.preco = element.preco_manual;
        checkboxSelecionar.id = 'checkSelecionar-'+element.id_manual;
        checkboxSelecionar.dataset.id_manual = element.id_manual;
        checkboxSelecionar.classList.add('checkbox-selecionar');
        checkboxSelecionar.addEventListener('change', function(){
            // Desmarca automaticamente o voucher se o manual for desmarcado
            if(this.checked == false){
                let checkVoucher = document.getElementById(`checkVoucher-${element.id_manual}`);
                if(checkVoucher){
                    checkVoucher.checked = false;
                }

                selecionarAll.checked = false;
                voucherAll.checked = false;
            }
            atualizarTotal();
        });

        let labelSelecionar = document.createElement('label');
        labelSelecionar.htmlFor = 'checkSelecionar-'+element.id_manual;

        selecionar.appendChild(checkboxSelecionar);
        selecionar.appendChild(labelSelecionar);
        linha.appendChild(selecionar);

        // Campo voucher
        if(element.tipo_manual == "Manual"){
            let voucher = document.createElement('td');
    
            let checkboxVoucher = document.createElement('input');
            checkboxVoucher.type = "checkbox";
            checkboxVoucher.dataset.preco = element.preco_manual;
            checkboxVoucher.dataset.id_manual = element.id_manual;
            checkboxVoucher.id = 'checkVoucher-'+element.id_manual;
            checkboxVoucher.classList.add('checkbox-voucher');
            checkboxVoucher.addEventListener('change', function(){
                
                // Seleciona automaticamente o manual
                if(this.checked == true){
                    let id_manual = checkboxVoucher.dataset.id_manual;                
                    document.getElementById(`checkSelecionar-${id_manual}`).checked = true;
                }
                else{
                    voucherAll.checked = false;
                }

                atualizarTotal();
            });
    
            let labelVoucher = document.createElement('label');
            labelVoucher.htmlFor = 'checkVoucher-'+element.id_manual;
    
            voucher.appendChild(checkboxVoucher);
            voucher.appendChild(labelVoucher);
            linha.appendChild(voucher);
        }
        else{
            linha.appendChild(document.createElement('td'));
        }


        tbody.appendChild(linha);
    })
}


// Vai buscar os manuais que correspondem aos filtros à base de dados 
async function filtrarManuais(agrupamento, ano_escolar, tipo_manual){
    let response = await fetch('encomendar_manuais.php', {
        method:"post",
        headers:{ 'Content-Type': 'application/json' },
        body: JSON.stringify({
            'acao':'filtrar_manuais',    
            'agrupamento':agrupamento,
            'ano_escolar':ano_escolar,
            'tipo_manual':tipo_manual

        })
    });

    const manuais  = await response.json();
    return manuais;    
}


function mostrarMsg(cor, conteudo, span = 'errorMsg') {
    const msg = document.getElementById(span);
    msg.style.color = cor;
    msg.innerText = conteudo;

    setTimeout(() => {
        msg.innerText = "";
    }, 2000)
}