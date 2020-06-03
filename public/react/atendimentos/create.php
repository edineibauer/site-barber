<?php

$read = new \Conn\Read();
$read->exeRead("clientes", "WHERE id = :id", "id={$dados['cliente']}");
if($read->getResult()) {
    $cliente = $read->getResult()[0];

    /**
     * Desconta valor resgatado (caso haja)
     */
    $valor = (!empty($cliente['valor']) && $cliente['valor'] > 0 ? ((float) $cliente['valor'] - $dados['valor_resgatado']) : $cliente['valor']);

    /**
     * Busca sistema de fidelidade
     */
    $read->exeRead("fidelizacao", "WHERE data_de_inicio < NOW() && data_de_termino > NOW()");
    if($read->getResult()) {
        $fidelizacoes = $read->getResult();
        foreach ($fidelizacoes as $fidelizacao) {
            $read->exeRead("atendimentos", "WHERE cliente = :c && dia_do_atendimento >= {$fidelizacao['data_de_inicio']}" . (!empty($fidelizacao['servicos_participantes']) ? " && servico IN(" . implode(", ", json_decode($fidelizacao['servicos_participantes'], !0)) . ")" : ""), "c={$cliente['id']}");
            if($read->getResult() && $read->getRowCount() === (int) $fidelizacao['atendimentos_necessarios']) {

                $valor += (float) $fidelizacao['valor_que_o_cliente_ganha'];

                /**
                 * Notifica
                 */
                if($_SESSION['userlogin']['setor'] === "administrador") {

                    /**
                     * Notifica apenas o administrador logado
                     */
                    \Dashboard\Notification::create($cliente['nome'] . " Ganhou R$" . $fidelizacao['valor_que_o_cliente_ganha'], "Parabéns, seu {$fidelizacao['atendimentos_necessarios']}° atendimento no sistema de fidelização '{$fidelizacao['nome']}'", $_SESSION['userlogin']['id']);
                } elseif($_SESSION['userlogin']['setor'] === "barbeiro") {

                    /**
                     * Notifica todos os administradores
                     */
                    $read->exeRead("administrador", "WHERE ativo = 1 && barbearia = :b", "b={$_SESSION['userlogin']['system_id']}", !0);
                    if($read->getResult()) {
                        foreach ($read->getResult() as $item)
                            \Dashboard\Notification::create($cliente['nome'] . " Ganhou R$" . $fidelizacao['valor_que_o_cliente_ganha'], "Parabéns, seu {$fidelizacao['atendimentos_necessarios']}° atendimento no sistema de fidelização '{$fidelizacao['nome']}'", $item['usuarios_id']);
                    }
                }
                break;
            }
        }
    }

    $up = new \Conn\Update();
    $up->exeUpdate("clientes", ["valor" => $valor], "WHERE id = :id", "id={$dados['cliente']}");
}