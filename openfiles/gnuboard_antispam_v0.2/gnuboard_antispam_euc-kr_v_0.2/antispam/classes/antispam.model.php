<?php

	require_once("./_common.php");
	require_once("$base/antispam/db/DB.class.php");

	class antispamModel{

		/* ���� ��å ���� DB�� ���� */
		function setantispamAdminConfig($args) {
			$oDB = &DB::getInstance();
		    $output = $oDB->executeQuery('antispam.deleteAdmConfig', $args);
			if(!$output) return $output;
            $output = $oDB->executeQuery('antispam.insertAdmConfig', $args);
            return $output;
		}
		
		/* xml�� DB�� ��� */
		function getDBbyXML($xml, $args=null){
			$oDB = &DB::getInstance();
			return $oDB->executeQuery($xml, $args);
		}

		/* �÷��߰� */
		function addColumn($table, $field, $type, $size){
			$oDB = &DB::getInstance();
			return $oDB->addColumn($table, $field, $type, $size, "0", "0");
		}

	}
?>
