<?php 

namespace MF\Model;

use Exception;
use src\Conexao;

class SQLActions {

    private $con;
    private $family_user = null;

    private function getFamilyUser()
    {
        return $this->family_user;
    }

    private function iniciarConexao()
    {
        $this->con = Conexao::getDb();

        return $this->con;
    }

    public function fecharConexao()
    {
        $this->con = NULL;
    }

    private function setFamilyUser($family)
    {
        $this->family_user = $family;

        return $this->family_user;
    }

    private function defineFamilyUser()
    {
        try {
            // if (!isset($_SESSION)) {
            //     session_start();
            // }
    
            if (isset($_SESSION['id_familia']) && !empty($_SESSION['id_familia'])) {
                $this->setFamilyUser($_SESSION['id_familia']);
            } else {
                $query = 'SELECT idFamilia FROM usuarios WHERE idUsuario = ?';
                $ret = $this->executarQuery($query, [$_SESSION['user']], false);

                if (empty($ret)) {
                    throw new Exception('idFamilia não encontrado.');
                }
    
                $_SESSION['id_familia'] = $ret[0]['idFamilia'];
                $this->setFamilyUser($ret[0]['idFamilia']);
            }

            return $this->getFamilyUser();

        } catch (Exception $e) {
            echo 'Security fail: ' . $e->getMessage();
            exit;
        }
    }

    private function setWhereSecurity($operacao, $query)
    {
        $id_family = $this->defineFamilyUser();
        /**
         * TODO: falta delete e update;
         */
        if ($operacao == 'SELECT' || $operacao == 'SHOW') {
            try {
                $arr_query = explode(' ', $query);
                $from_key = array_search('FROM', $arr_query);
                $table = $arr_query[$from_key + 1];

                if ($from_key == false) {
                    throw new Exception('FROM clause not found');
                }
    
                $where_key = array_search('WHERE', $arr_query);
                if ($where_key !== false) {
                    $id_into_where = " ($table.idFamilia = $id_family) AND ";
                    $arr_query[$where_key] .= $id_into_where;
    
                    $query = implode(' ', $arr_query);
                } else {
                    throw new Exception('WHERE clause not found');
                }
            } catch (Exception $e) {
                echo 'Security fail: ' . $e->getMessage();
                exit;
            }
        }

        if ($operacao == 'INSERT') {
            $arr_query = explode(')', $query);

            $arr_query[0] .= ', idFamilia)';
            $arr_query[1] .= ', ' . $id_family . ')';

            $query = $arr_query[0] . $arr_query[1];
        }

        return $query;
    }

	public function executarQuery($query, $arr_values = [], $apply_security = true)
    {
        /**
         * remove 'enters' dados na string.
         */
        $query = trim(preg_replace('/\s\s+/', ' ', $query));

        $operacao = strtok($query, ' ');

        /**
         * TODO: falta delete e update;
         */
        if ($apply_security) {
            $query = $this->setWhereSecurity($operacao, $query);
        }

        $bd = $this->iniciarConexao();
        $stmt = $bd->prepare($query);

        try {
            
            $bd->beginTransaction();

            if (!empty($arr_values))
                $stmt->execute($arr_values);
            else
                $stmt->execute();

            //$stmt->debugDumpParams();
            //exit;
            switch ($operacao) {
                case 'INSERT':
                    $result = $bd->lastInsertId();
                    break;
                case 'UPDATE':
                case 'DELETE':
                    $result = $stmt->rowCount();
                    break;
                case 'SELECT':
                case 'SHOW':
                    $retornar_select = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                    $result = $retornar_select;
                    break;
                default:
                    throw new Exception('Operação não reconhecida.');
            }

            $bd->commit();
            $bd = NULL;

            return $result;
        } catch (Exception $e) {
            $bd->rollBack();

            errorHandler(
				1, 
				$e->getMessage(),
				$e->getFile(),
				$e->getLine()
			);

            exit;
        }
    }
}