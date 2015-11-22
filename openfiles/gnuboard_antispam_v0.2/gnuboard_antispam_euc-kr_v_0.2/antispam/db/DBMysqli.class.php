<?php
	require_once('DBMysql.class.php');
    /**
     * @class DBMysqli
     * @author NHN (developers@xpressengine.com)
     * @brief MySQL DBMS�� mysqli_* �� �̿��ϱ� ���� class
     * @version 0.1
     *
     * mysql handling class
     **/
	

    class DBMysqli extends DBMysql {

        /**
         * @brief constructor
         **/
        function DBMysqli() {
            $this->_setDBInfo();
            $this->_connect();
        }

        /**
         * @brief ��ġ ���� ���θ� return
         **/
        function isSupported() {
            if(!function_exists('mysqli_connect')) return false;
            return true;
        }
		
		/**
		 * @brief create an instance of this class
		 */
		function create()
		{
			return new DBMysqli;
		}

        /**
         * @brief DB ����
         **/
        function _connect() {
            // db ������ ������ ����
            if(!$this->hostname || !$this->userid || !$this->password || !$this->database) return;

            // ���ӽõ�
			if($this->port){
	            $this->fd = @mysqli_connect($this->hostname, $this->userid, $this->password, $this->database, $this->port);
			}else{
	            $this->fd = @mysqli_connect($this->hostname, $this->userid, $this->password, $this->database);
			}
			$error = mysqli_connect_errno();
            if($error) {
                $this->setError($error,mysqli_connect_error());
                return;
            }
			mysqli_set_charset($this->fd,'utf8');

            // ����üũ
            $this->is_connected = true;
			$this->password = md5($this->password);
        }

        /**
         * @brief DB���� ����
         **/
        function close() {
            if(!$this->isConnected()) return;
            mysqli_close($this->fd);
        }

        /**
         * @brief �������� �ԷµǴ� ���ڿ� �������� quotation ����
         **/
        function addQuotes($string) {
            if(version_compare(PHP_VERSION, "5.9.0", "<") && get_magic_quotes_gpc()) $string = stripslashes(str_replace("\\","\\\\",$string));
            if(!is_numeric($string)) $string = mysqli_escape_string($this->fd,$string);
            return $string;
        }

        /**
         * @brief : �������� ���� �� ����� fetch ó��
         *
         * query : query�� �����ϰ� result return\n
         * fetch : reutrn �� ���� ������ NULL\n
         *         rows�̸� array object\n
         *         row�̸� object\n
         *         return\n
         **/
        function _query($query) {
            if(!$this->isConnected()) return;

            // ���� ������ �˸�
            $this->actStart($query);

            // ���� �� ����
            $result = mysqli_query($this->fd,$query);
            // ���� üũ
			$error = mysqli_error($this->fd);
            if($error){
				$this->setError(mysqli_errno($this->fd), $error);
			}

            // ���� ���� ���Ḧ �˸�
            $this->actFinish();

            // ��� ����
            return $result;
        }

		function db_insert_id()
		{
            return  mysqli_insert_id($this->fd);
		}

		function db_fetch_object(&$result)
		{
			return mysqli_fetch_object($result);
		}
    }

return new DBMysqli;
?>
