<?php
	
	$dados = explode(':', $_GET['dados']);
	$api_key = $_GET['api_key'];

	// coleta os dados dos usuarios
	$usuarios = json_decode(file_get_contents('usuarios_api.json'), true)['usuarios'];

	// verifica key
	$usuario_id = keyValida($api_key, $usuarios);

	if ($usuario_id == -1) {
		header('Content-type: application/json', '', 400);
		echo json_encode(['mensagem' => '400, Api Key Invalida']);
		exit;
	}

	//coleta dados do bd
	$bd = json_decode(file_get_contents('db.json'), true);

	// coleta metodo do request
	$metodo = $_SERVER['REQUEST_METHOD'];

	//verifica se o usuario tem autorizacao para o metodo requestado
	if (!array_search($metodo, $usuarios[$usuario_id]['metodos'])) {
		
		header('Content-type: application/json', '', 403);
		echo json_encode(['mensagem' => '403, Usuario Sem Permissao']);
	} else {
		
		// GET
		if ($metodo === 'GET') {
			header('Content-type: application/json');

			if ($bd[$dados[0]]) {
				echo json_encode($bd[$dados[0]]);
			} else {
				// imprime todos os daos
				// echo json_encode($bd);
				
				echo '[]';
			}
		}// GET 

		// POST
		if ($metodo === 'POST') {

			$corpo = file_get_contents('php://input');

			// Cria obj do novo post
			$objCorpo = json_decode($corpo, true);

			// caso nenhum json tenha sido passado
			if (count($objCorpo) < 1) {
				header('Content-type: application/json', '', 400);
				echo json_encode(['mensagem' => '400, Nao Inserido']);
				exit;
			}

			// Caso o dado nao existe
			if (!$bd[$dados[0]]) {
				$bd[$dados[0]] = [];
			}

			// adiciona o novo ao $bd
			if (count($objCorpo) == 1) {
				
				// id pro novo obj
				// $bd['id'] = time();

				// apenas um item
				$bd[$dados[0]][] = $objCorpo;
				$n_item = 1;

			// para mais de um item
			} else {
				foreach ($objCorpo as $n_item => $value) {
					$bd[$dados[0]][$n_item] = $value;
				}
			}
			
			// salva o novo arquivo
			file_put_contents('db.json', json_encode($bd));

			header('Content-type: application/json');
			echo json_encode(['mensagem' => '200, ('.($n_item+1).') Inserido(s).']);
		}// POST
	}// else permissao
	
	//Metodo para verificar se a key do usuario e' valida
	function keyValida($api_key, $arr){
		foreach ($arr as $id => $value) {
			if ($api_key == $value['api_key']) {
				return $id;
			}
		}

		return -1;
	}//function keyValida(
?>