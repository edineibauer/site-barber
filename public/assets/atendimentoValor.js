var cliente = {id: 0};
var servico = {id: 0};
var valor = -1;
var resgateBefore = (isNumberPositive(form.data.valor_resgatado) ? parseFloat(form.data.valor_resgatado) : 0);

if (isEmpty(form.id)) {

    /**
     * Busca todos os relatórios, e usa o relatório de ultimos serviços para
     * encontrar o serviço mais usado e já pré setar
     */
    db.exeRead("relatorios").then(reports => {
        if (!isEmpty(reports)) {
            for (let report of reports) {
                if (report.nome === "Serviços último mês") {
                    let gs = isEmpty(report.soma) ? [] : JSON.parse(report.soma);
                    let gm = isEmpty(report.media) ? [] : JSON.parse(report.media);
                    let gma = isEmpty(report.maior) ? [] : JSON.parse(report.maior);
                    let gme = isEmpty(report.menor) ? [] : JSON.parse(report.menor);
                    reportRead(report.entidade, report.search, report.regras, report.agrupamento, gs, gm, gma, gme, report.ordem, report.decrescente, 1, -1)
                        .then(dados => {
                            if (!isEmpty(dados.data))
                                addListSetTitle(form, "servicos", "servico", "", dados.data[0].servico, $("#servico").parent());
                        });
                    break;
                }
            }
        }
    });
}

var intervalo = setInterval(function () {
    (async () => {
        if (typeof form?.data === "undefined") {
            clearInterval(intervalo);
            return;
        }

        if (!isEmpty(form.data.cliente) && form.data.cliente != cliente.id)
            cliente = await db.exeRead("clientes", form.data.cliente);
        else if (isEmpty(form.data.cliente))
            cliente = {id: 0};

        if (!isEmpty(form.data.servico) && form.data.servico != servico.id)
            servico = await db.exeRead("servicos", form.data.servico);
        else if (isEmpty(form.data.servico))
            servico = {id: 0};

        if (cliente.id > 0 && servico.id > 0) {
            let valorServico = parseFloat(form.data.promocao ? servico.valor_em_promocao : servico.valor);
            let clienteValor = parseFloat(cliente.valor) + resgateBefore;
            form.data.valor_resgatado = (clienteValor > 0 ? (clienteValor > valorServico ? valorServico : clienteValor) : 0);
            valor = valorServico - form.data.valor_resgatado;

            if (valor !== form.data.valor_do_atendimento) {
                form.data.valor_do_atendimento = valor;
                $("#valor_do_atendimento").val(form.data.valor_do_atendimento.toFixed(2).replace(".", ","));
            }
        } else {
            valor = -1;
            form.data.valor_resgatado = 0;
            form.data.valor_do_atendimento = "";
            $("#valor_do_atendimento").val("");
        }
    })();
}, 300);