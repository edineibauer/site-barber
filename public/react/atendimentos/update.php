<?php

$read = new \Conn\Read();
$read->exeRead("clientes", "WHERE id = :id", "id={$dados['cliente']}");
if ($read->getResult()) {
    $cliente = $read->getResult()[0];

    $valorResgate = (float)$dados['valor_resgatado'] - $dadosOld['valor_resgatado'];
    if ($valorResgate > 0) {
        /**
         * Cobra valor extra do cliente
         */
        $valorCliente = (((float)$cliente['valor']) - ((float)$valorResgate));
        $valorCliente = $valorCliente < 0 ? 0 : $valorCliente;

    } else {
        /**
         * Da a diferença em créditos para o cliente
         */
        $valorCliente = ((float)$cliente['valor']) + ($valorResgate * -1);
    }

    $up = new \Conn\Update();
    $up->exeUpdate("clientes", ["valor" => $valorCliente], "WHERE id = :id", "id={$dados['cliente']}");
}
