<?
	$base = $g4[admin_path];
	require_once("$base/../head.sub.php");
	require_once("$base/antispam/classes/antispam.admin.view.php");
	
	function isSpam($id, $ip, $content, $subject=""){
		require("../adm/antispam/lang/lang.php");
		$guestId = "Guest";
		$obj = new antispamAdminView();
	
		
		
		//IP�˻�
		if( true == $obj->isSpammer($id, $ip)  ){
			if( $id == $guestId ){
				$id = null;
			}
			//���и� ����Ʈ ������Ʈ
			$obj->updateBlackList($id, $ip, $content, $subject);
			$config = $obj->dispantispamAdminConfig();
			$phonemail = "(������ ����ó : ".$config->data->phone1."-".$config->data->phone2."-".$config->data->phone3.", ".$config->data->mail1."@".$config->data->mail2.".".$config->data->mail3.")";
			echo "<script>alert('$lang->alert_is_spammer\\n$phonemail');javascript:history.go(-2);</script>";
			return true;	
		}else{
			$spam = $obj->dispSpam($content.$subject);

			if( $spam == null ){
				return false;
			}else{
				if( $id == $guestId ){
					$id = null;
				}

				//���Էα�
				$obj->insertContentInLog($spam->score, $spam->type, $content, $id, date('Y-m-d H:i:s'), $ip);

				//���и� ����Ʈ ������Ʈ
				$obj->updateBlackList($id, $ip, $content, $subject, $spam->type, $spam->score);
				$config = $obj->dispantispamAdminConfig();
				$phonemail = "(������ ����ó : ".$config->data->phone1."-".$config->data->phone2."-".$config->data->phone3.", ".$config->data->mail1."@".$config->data->mail2.".".$config->data->mail3.")";
				echo "<script>alert('$lang->alert_is_spam\\n$phonemail');javascript:history.go(-2);</script>";
				return true;
			}
		}
	}
?>
