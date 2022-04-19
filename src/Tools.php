<?php
namespace NFService\PlugConta;

use Exception;

/**
 * Classe Tools
 *
 * Classe responsável pela comunicação com a API Tecnospeed
 *
 * @category  NFService
 * @package   NFService\PlugConta\Tools
 * @author    Diego Almeida <diego.feres82 at gmail dot com>
 * @copyright 2022 NFSERVICE
 * @license   https://opensource.org/licenses/MIT MIT
 */
class Tools
{
    /**
     * Variável responsável por armazenar os dados a serem utilizados para comunicação com a API
     * Dados como token, cnpj, ambiente(produção ou homologação)
     *
     * @var array
     */
    private $config = [
        'cnpjsh' => '',
        'tokensh' => '',
        'production' => false,
        'debug' => false,
        'upload' => false,
        'decode' => true
    ];

    /**
     * Metodo contrutor da classe
     *
     * @param boolean $isProduction Define se o ambiente é produção
     */
    public function __construct(bool $isProduction = true)
    {
        $this->setProduction($isProduction);
    }

    /**
     * Define se a classe deve se comunicar com API de Produção ou com a API de Homologação
     *
     * @param bool $isProduction Boleano para definir se é produção ou não
     *
     * @access public
     * @return void
     */
    public function setProduction(bool $isProduction) :void
    {
        $this->config['production'] = $isProduction;
    }

    /**
     * Função responsável por setar o CNPJ a ser utilizado na comunicação com a API do Pagamentos
     *
     * @param string $cnpj CNPJ da SofterHouse
     *
     * @access public
     * @return void
     */
    public function setCnpj(string $cnpj) :void
    {
        $this->config['cnpjsh'] = $cnpj;
    }

    /**
     * Função responsável por setar o token a ser utilizada na comunicação com a API do Pagamentos
     *
     * @param string $token Token da SofterHouse
     *
     * @access public
     * @return void
     */
    public function setToken(string $token) :void
    {
        $this->config['tokensh'] = $token;
    }

    /**
     * Define se a classe realizará um upload
     *
     * @param bool $isUpload Boleano para definir se é upload ou não
     *
     * @access public
     * @return void
     */
    public function setUpload(bool $isUpload) :void
    {
        $this->config['upload'] = $isUpload;
    }

    /**
     * Define se a classe realizará o decode do retorno
     *
     * @param bool $decode Boleano para definir se fa decode ou não
     *
     * @access public
     * @return void
     */
    public function setDecode(bool $decode) :void
    {
        $this->config['decode'] = $decode;
    }

    /**
     * Retorna se o ambiente setado é produção ou não
     *
     *
     * @access public
     * @return bool
     */
    public function getProduction() : bool
    {
        return $this->config['production'];
    }

    /**
     * Recupera o cnpj setado na comunicação com a API
     *
     * @access public
     * @return string
     */
    public function getCnpj() :string
    {
        return $this->config['cnpjsh'];
    }

    /**
     * Recupera o token setado na comunicação com a API
     *
     * @access public
     * @return string
     */
    public function getToken() :string
    {
        return $this->config['tokensh'];
    }

    /**
     * Recupera se é upload ou não
     *
     *
     * @access public
     * @return bool
     */
    public function getUpload() : bool
    {
        return $this->config['upload'];
    }

    /**
     * Recupera se faz decode ou não
     *
     *
     * @access public
     * @return bool
     */
    public function getDecode() : bool
    {
        return $this->config['decode'];
    }

    /**
     * Função responsável por definir se está em modo de debug ou não a comunicação com a API
     * Utilizado para pegar informações da requisição
     *
     * @param bool $isDebug Boleano para definir se é produção ou não
     *
     * @access public
     * @return void
     */
    public function setDebug(bool $isDebug) : void
    {
        $this->config['debug'] = $isDebug;
    }

    /**
     * Retorna os cabeçalhos padrão para comunicação com a API
     *
     * @access private
     * @return array
     */
    private function getDefaultHeaders() :array
    {
        $headers = [
            'cnpjsh: '.$this->config['cnpjsh'],
            'tokensh: '.$this->config['tokensh'],
        ];

        if (!$this->config['upload']) {
            $headers[] = 'Content-Type: application/json';
        } else {
            $headers[] = 'Content-Type: multipart/form-data';
        }

        return $headers;
    }

    /**
     * Função responsável por retornar os dados de um pagador
     *
     * @param string $cpfcnpj CPF/CNPJ do pagador
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function consultaPagador(string $cpfcnpj, array $params = []) :array
    {
        try {
            $headers = [
                'payercpfcnpj: '.onlyNumber($cpfcnpj)
            ];

            $dados = $this->get('payer', $params, $headers);

            if (in_array($dados['httpCode'], [200, 201, 202])) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                $errors[] = $dados['body']->message;

                if (isset($dados['body']->errors)) {
                    foreach ($dados['body']->errors as $error) {
                        $errors[] = $error->message;
                    }
                }

                throw new Exception("\r\n".implode("\r\n", $errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por cadastrar um novo pagador
     *
     * @param array $dados Dados do pagador
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function cadastraPagador(array $dados, array $params = []) :array
    {
        try {
            $dados = $this->post('payer', $dados, $params);

            if (in_array($dados['httpCode'], [200, 201, 202])) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                $errors[] = $dados['body']->message;

                if (isset($dados['body']->errors)) {
                    foreach ($dados['body']->errors as $error) {
                        $errors[] = $error->message;
                    }
                }

                throw new Exception("\r\n".implode("\r\n", $errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por atualizar um pagador
     *
     * @param string $cpfcnpj CPF/CNPJ do pagador
     * @param array $dados Dados do pagador
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function atualizaPagador(string $cpfcnpj, array $dados, array $params = []) :array
    {
        try {
            $headers = [
                'payercpfcnpj: '.onlyNumber($cpfcnpj)
            ];

            $dados = $this->put('payer', $dados, $params, $headers);

            if (in_array($dados['httpCode'], [200, 201, 202])) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                $errors[] = $dados['body']->message;

                if (isset($dados['body']->errors)) {
                    foreach ($dados['body']->errors as $error) {
                        $errors[] = $error->message;
                    }
                }

                throw new Exception("\r\n".implode("\r\n", $errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por listar as contas bancárias de um pagador
     *
     * @param string $cpfcnpj CPF/CNPJ do pagador
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function listaContas(string $cpfcnpj, array $params = []) :array
    {
        try {
            $headers = [
                'payercpfcnpj: '.onlyNumber($cpfcnpj)
            ];

            $dados = $this->get('account', $params, $headers);

            if (in_array($dados['httpCode'], [200, 201, 202])) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                $errors[] = $dados['body']->message;

                if (isset($dados['body']->errors)) {
                    foreach ($dados['body']->errors as $error) {
                        $errors[] = $error->message;
                    }
                }

                throw new Exception("\r\n".implode("\r\n", $errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por criar uma nova conta bancária
     *
     * @param string $cpfcnpj CPF/CNPJ do pagador
     * @param array $dados Dados da conta bancária
     *
     * @access public
     * @return array
     */
    public function cadastraConta(string $cpfcnpj, array $dados, array $params = []) :array
    {
        try {
            $headers = [
                'payercpfcnpj: '.onlyNumber($cpfcnpj)
            ];

            $dados = $this->post('account', $dados, $params, $headers);

            if (in_array($dados['httpCode'], [200, 201, 202])) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                $errors[] = $dados['body']->message;

                if (isset($dados['body']->errors)) {
                    foreach ($dados['body']->errors as $error) {
                        $errors[] = $error->message;
                    }
                }

                throw new Exception("\r\n".implode("\r\n", $errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por consultar os dados de uma conta bancária
     *
     * @param string $hash Hash da conta bancária na Tecnospeed
     * @param string $cpfcnpj CPF/CNPJ do pagador
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function consultaConta(string $hash, string $cpfcnpj, array $params = []) :array
    {
        try {
            $headers = [
                'payercpfcnpj: '.onlyNumber($cpfcnpj)
            ];

            $dados = $this->get("account/$hash", $params, $headers);

            if (in_array($dados['httpCode'], [200, 201, 202])) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                $errors[] = $dados['body']->message;

                if (isset($dados['body']->errors)) {
                    foreach ($dados['body']->errors as $error) {
                        $errors[] = $error->message;
                    }
                }

                throw new Exception("\r\n".implode("\r\n", $errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por atualizar uma conta bancária
     *
     * @param string $hash Hash da conta bancária na Tecnospeed
     * @param string $cpfcnpj CPF/CNPJ do pagador
     * @param array $dados Dados da conta bancária
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function atualizaConta(string $hash, string $cpfcnpj, array $dados, array $params = []) :array
    {
        try {
            $headers = [
                'payercpfcnpj: '.onlyNumber($cpfcnpj)
            ];

            $dados = $this->put("account/$hash", $dados, $params, $headers);

            if (in_array($dados['httpCode'], [200, 201, 202])) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                $errors[] = $dados['body']->message;

                if (isset($dados['body']->errors)) {
                    foreach ($dados['body']->errors as $error) {
                        $errors[] = $error->message;
                    }
                }

                throw new Exception("\r\n".implode("\r\n", $errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por enviar um extrato bancário
     *
     * @param string $cpfcnpj CPF/CNPJ do pagador
     * @param \CURLFile $file Arquivo do extrato
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function enviaExtrato(string $cpfcnpj, \CURLFile $file, array $params = []) :array
    {
        try {
            $headers = [
                'payercpfcnpj: '.onlyNumber($cpfcnpj)
            ];

            $dados = [
                'file' => $file
            ];

            $originalUpload = $this->getUpload();
            $this->setUpload(true);

            $dados = $this->post('statement/parser', $dados, $params, $headers);

            $this->setUpload($originalUpload);

            if (in_array($dados['httpCode'], [200, 201, 202])) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                $errors[] = $dados['body']->message;

                if (isset($dados['body']->errors)) {
                    foreach ($dados['body']->errors as $error) {
                        $errors[] = $error->message;
                    }
                }

                throw new Exception("\r\n".implode("\r\n", $errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por consultar os dados de um extrato bancário
     *
     * @param string $id ID do extrato bancário
     * @param string $cpfcnpj CPF/CNPJ do pagador
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function consultaExtrato(string $id, string $cpfcnpj, array $params = []) :array
    {
        try {
            $headers = [
                'payercpfcnpj: '.onlyNumber($cpfcnpj)
            ];

            $dados = $this->get("statement/parser/$id", $params, $headers);

            if (in_array($dados['httpCode'], [200, 201, 202])) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                $errors[] = $dados['body']->message;

                if (isset($dados['body']->errors)) {
                    foreach ($dados['body']->errors as $error) {
                        $errors[] = $error->message;
                    }
                }

                throw new Exception("\r\n".implode("\r\n", $errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por consultar extrato por período
     *
     * @param string $cpfcnpj CPF/CNPJ do pagador
     * @param string $date_start Data de início do período
     * @param string $date_end Data de término do período
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function consultaExtratoPeriodo(string $cpfcnpj, string $date_start, string $date_end, array $params = []) :array
    {
        try {
            $params = array_filter($params, function($item) {
                return !in_array($item['name'], ['dateStart', 'dateEnd']);
            }, ARRAY_FILTER_USE_BOTH);

            $params[] = [
                'name' => 'dateStart',
                'value' => $date_start
            ];
            $params[] = [
                'name' => 'dateEnd',
                'value' => $date_end
            ];

            $headers = [
                'payercpfcnpj: '.onlyNumber($cpfcnpj)
            ];

            $dados = $this->get('statement', $params, $headers);

            if (in_array($dados['httpCode'], [200, 201, 202])) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                $errors[] = $dados['body']->message;

                if (isset($dados['body']->errors)) {
                    foreach ($dados['body']->errors as $error) {
                        $errors[] = $error->message;
                    }
                }

                throw new Exception("\r\n".implode("\r\n", $errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Função responsável por retornar o conteúdo de um arquivo de extrato
     *
     * @param string $id ID do extrato bancário
     * @param string $cpfcnpj CPF/CNPJ do pagador
     * @param array $params Parametros adicionais para a requisição
     *
     * @access public
     * @return array
     */
    public function baixaExtrato(string $id, string $cpfcnpj, array $params = []) :array
    {
        try {
            $headers = [
                'payercpfcnpj: '.onlyNumber($cpfcnpj)
            ];

            $originalDecode = $this->getDecode();
            $this->setDecode(false);

            $dados = $this->get("statement/$id/download", $params, $headers);

            $this->setDecode($originalDecode);

            if (in_array($dados['httpCode'], [200, 201, 202])) {
                return $dados;
            }

            if (isset($dados['body']->message)) {
                $errors[] = $dados['body']->message;

                if (isset($dados['body']->errors)) {
                    foreach ($dados['body']->errors as $error) {
                        $errors[] = $error->message;
                    }
                }

                throw new Exception("\r\n".implode("\r\n", $errors), 1);
            }

            throw new Exception(json_encode($dados), 1);
        } catch (Exception $error) {
            throw new Exception($error, 1);
        }
    }

    /**
     * Execute a GET Request
     *
     * @param string $path
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     * @return array
     */
    private function get(string $path, array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_HTTPHEADER => $this->getDefaultHeaders()
        ];

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = array_merge($opts[CURLOPT_HTTPHEADER], $headers);
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a POST Request
     *
     * @param string $path
     * @param string $body
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     * @return array
     */
    private function post(string $path, array $body = [], array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => !$this->config['upload'] ? json_encode($body) : $this->convertToFormData($body),
            CURLOPT_HTTPHEADER => $this->getDefaultHeaders()
        ];

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = array_merge($opts[CURLOPT_HTTPHEADER], $headers);
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a PUT Request
     *
     * @param string $path
     * @param string $body
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     * @return array
     */
    private function put(string $path, array $body = [], array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_HTTPHEADER => $this->getDefaultHeaders(),
            CURLOPT_CUSTOMREQUEST => "PUT",
            CURLOPT_POSTFIELDS => !$this->config['upload'] ? json_encode($body) : $this->convertToFormData($body)
        ];

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = array_merge($opts[CURLOPT_HTTPHEADER], $headers);
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a DELETE Request
     *
     * @param string $path
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     * @return array
     */
    private function delete(string $path, array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_HTTPHEADER => $this->getDefaultHeaders(),
            CURLOPT_CUSTOMREQUEST => "DELETE"
        ];

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = array_merge($opts[CURLOPT_HTTPHEADER], $headers);
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Execute a OPTION Request
     *
     * @param string $path
     * @param array $params
     * @param array $headers Cabeçalhos adicionais para requisição
     * @return array
     */
    private function options(string $path, array $params = [], array $headers = []) :array
    {
        $opts = [
            CURLOPT_CUSTOMREQUEST => "OPTIONS"
        ];

        if (!empty($headers)) {
            $opts[CURLOPT_HTTPHEADER] = $headers;
        }

        $exec = $this->execute($path, $opts, $params);

        return $exec;
    }

    /**
     * Função responsável por realizar a requisição e devolver os dados
     *
     * @param string $path Rota a ser acessada
     * @param array $opts Opções do CURL
     * @param array $params Parametros query a serem passados para requisição
     *
     * @access private
     * @return array
     */
    private function execute(string $path, array $opts = [], array $params = []) :array
    {
        if (!preg_match("/^\//", $path)) {
            $path = '/' . $path;
        }

        $url = 'https://api.pagamentobancario.com.br/api/v1';
        if (!$this->config['production']) {
            $url = 'https://staging.pagamentobancario.com.br/api/v1';
        }
        $url .= $path;

        $curlC = curl_init();

        if (!empty($opts)) {
            curl_setopt_array($curlC, $opts);
        }

        if (!empty($params)) {
            $paramsJoined = [];

            foreach ($params as $param) {
                if (isset($param['name']) && !empty($param['name']) && isset($param['value']) && !empty($param['value'])) {
                    $paramsJoined[] = urlencode($param['name'])."=".urlencode($param['value']);
                }
            }

            if (!empty($paramsJoined)) {
                $params = '?'.implode('&', $paramsJoined);
                $url = $url.$params;
            }
        }

        curl_setopt($curlC, CURLOPT_URL, $url);
        curl_setopt($curlC, CURLOPT_RETURNTRANSFER, true);
        if (!empty($dados)) {
            curl_setopt($curlC, CURLOPT_POSTFIELDS, !$this->config['upload'] ? json_encode($dados) : $dados);
        }
        $retorno = curl_exec($curlC);
        $info = curl_getinfo($curlC);
        $return["body"] = ($this->config['decode'] || !in_array($info['http_code'], [200, 201, 202])) ? json_decode($retorno) : $retorno;
        $return["httpCode"] = curl_getinfo($curlC, CURLINFO_HTTP_CODE);
        if ($this->config['debug']) {
            $return['info'] = curl_getinfo($curlC);
        }
        curl_close($curlC);

        return $return;
    }

    /**
     * Função responsável por montar o corpo de uma requisição no formato aceito pelo FormData
     */
    private function convertToFormData($data)
    {
        $dados = [];

        $recursive = false;
        foreach ($data as $key => $value) {
            if (!is_array($value)) {
                $dados[$key] = $value;
            } else {
                foreach ($value as $subkey => $subvalue) {
                    $dados[$key.'['.$subkey.']'] = $subvalue;

                    if (is_array($subvalue)) {
                        $recursive = true;
                    }
                }
            }
        }

        if ($recursive) {
            return $this->convertToFormData($dados);
        }

        return $dados;
    }
}
