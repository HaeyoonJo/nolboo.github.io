<?php

	require_once("./_common.php");
	require_once("$base/antispam/classes/antispam.model.php");
	require_once("$base/antispam/libs/RequestGetSpamScores.class.php");
	require_once("$base/antispam/libs/RequestPutSpamReport.class.php");
	
	class antispamAdminController{
		
		/*	���� ���� */
		function procantispamAdminConfigUpdate($params) {

			$args->use_antispam = $params->use_antispam=='Y'?'Y':'N';
			$args->score_antispam = $params->score_antispam;
			$args->phone1 = $params->phone1;
			$args->phone2 = $params->phone2;
			$args->phone3 = $params->phone3;
			$args->mail1 = $params->mail1;
			$args->mail2 = $params->mail2;
			$args->mail3 = $params->mail3;
			$args->use_block_member = $params->use_block_member=='Y'?'Y':'N';
			$args->use_block_ip = $params->use_block_ip =='Y'?'Y':'N';
			$args->score_block_member = $params->score_block_member;
			$args->score_block_ip = $params->score_block_ip;
			$args->date_block_member = $params->date_block_member;
			$args->date_block_ip = $params->date_block_ip;

			$exception_word_list = array();
			$exception_word_list = explode('|@|',$params->exception_word_list);

			$args->exception_word_list = serialize($exception_word_list);

				

			$model = new antispamModel();
			
			/* ���и� ���� �ʱ�ȭ */
			/*
			$config = $model->getDBbyXML('antispam.getAdmConfig')->data;
			if( $config->use_block_member == 'N' && $args->use_block_member == 'Y' ){
				$model->getDBbyXML("antispam.deleteBlackListId", $param);
			}
			if( $config->use_block_ip == 'N' && $args->use_block_ip == 'Y'  ){
				$model->getDBbyXML("antispam.deleteBlackListIp", $param);
			}
			*/
			return $model->setantispamAdminConfig($args);
		}

		
		/* üũ �� �ۿ� ������å ���� */
		function applySpamConfig($check_list, $board) {
			//�Ľ��Ͽ� ���� �� ����
			$id_arr = array();
			$id_arr = explode('|@|',$check_list);
					
			if( !isset($id_arr) ){
				return 0;
			}

			//�𵨿��� ������ �������� ����
			$model = new antispamModel();
			$config = $model->getDBbyXML('antispam.getAdmConfig');
			if(	$config->data->use_antispam == "N" ){
				return 1;
			}
			foreach( $id_arr as $id ){
				//�ش� id�� ���� �ҷ���
				$arg->wr_id = $id;
				$content = $model->getDBbyXML("antispam.get$board", $arg);

				//�ش���� ���������� ��
				if( $content->data[1]->spam_score >= $config->data->score_antispam  ){
					//�̻��̸� ���Ժ��������� �̵�
					$this->insertContentInStore($content->data[1], $board);
					$this->deleteContent($board, $id);
				}
			}
			return 1;	
			//���� 1, ���� 0
		}
		
		/* �Ű�� �� ó�� */
		function sendSpamContents($check_list, $board){
			//�Ľ��Ͽ� ���� �� ����
			$id_arr = array();
			$id_arr = explode('|@|',$check_list);
					
			if( !isset($id_arr) ){
				return 0;
			}

			//�𵨿��� ������ �������� ����
			$model = new antispamModel();
			foreach( $id_arr as $id ){
				//�ش� id�� ���� �ҷ���
				$arg->wr_id = $id;
				$content = $model->getDBbyXML("antispam.get$board", $arg);
		
			

				/* �� �Ű� �ϴ� �κ� �߰� �ؾ� �� */



				//���Ժ��������� �̵�
				$this->insertContentInStore($content->data[1], $board);
				$this->deleteContent($board, $id);
				
			}
			return 1;	
			//���� 1, ���� 0
		}

			/* ���Ժ����Կ� �ִ� �� ���� */
		function deleteSpamcontents($check_list, $board) {
			//�Ľ��Ͽ� ���� �� ����
			$id_arr = array();
			$id_arr = explode('|@|',$check_list);
					
			if( !isset($id_arr) ){
				return 0;
			}

			$model = new antispamModel();
			foreach( $id_arr as $id ){
				//�ش� id�� ���� �ҷ���
				$args->board = $board;
				$args->wr_id = $id;

				//���Ա� ����
				$this->deleteStoreContent($board, $id);
			}
			return 1;	
			//���� 1, ���� 0
		}


		/* ���Էα׿� �ִ� �� ���� */
		function deleteLog($check_list, $board) {
			//�Ľ��Ͽ� ���� �� ����
			$id_arr = array();
			$id_arr = explode('|@|',$check_list);
					
			if( !isset($id_arr) ){
				return 0;
			}

			$model = new antispamModel();
			foreach( $id_arr as $id ){
				//�ش� id�� ���� �ҷ���
				//$args->board = $board;
				$args->wr_id = $id;

				//���Ա� ����
				$model->getDBbyXML("antispam.deleteLog", $args);

			}
			return 1;	
			//���� 1, ���� 0
		}



		/* üũ �� �� ���� */
		function restoreContent($check_list, $board) {
			//�Ľ��Ͽ� ���� �� ����
			$id_arr = array();
			$id_arr = explode('|@|',$check_list);
					
			if( !isset($id_arr) ){
				return 0;
			}

			$model = new antispamModel();
			foreach( $id_arr as $id ){
				//�ش� id�� ���� �ҷ���
				$args->board = $board;
				$args->wr_id = $id;

				//���Ժ��������� �̵�
				$content = $model->getDBbyXML("antispam.getStore", $args);
				$this->checkInsertXMLQuery($board);
				$this->insertContentDocumentlist($content->data[1], $board);
				$this->deleteStoreContent($board, $id);
			}
			return 1;	
			//���� 1, ���� 0
		}

		/* ���� ��ġ ���� */
	
		function deleteBlackList($check_list, $board) {
			//�Ľ��Ͽ� ���� �� ����
			$id_arr = array();
			$id_arr = explode('|@|',$check_list);
					
			if( !isset($id_arr) ){
				return 0;
			}

			$model = new antispamModel();
			foreach( $id_arr as $v ){
				//�ش� ���̵� ����
				
				$arg->user_id = $arg->user_ip = $v;
				$model->getDBbyXML("antispam.deleteBlack$board", $arg);
			}
			return 1;	
			//���� 1, ���� 0
		}
		
		
		/* ����ȸ�� ��� */
		/*
		function addBlockMember($id, $spam_type, $spam_score, $content, $subject){
			$args->user_id = $id;
			
			$args->spam_type = $spam_type;
			$args->spam_score = $spam_score;
			$args->subject = $subject;
			$args->content = $content;

			$args->user_ip = $_SERVER['REMOTE_ADDR'];
			$args->datetime = date('Y-m-d H:i:s');
			
			$args->manage = '��������'; //�׽�Ʈ(IP�� �Ұ��� �������� �Ұ��� ���ؾ���)

			$model = new antispamModel();
			
			//����
			$model->getDBbyXML("antispam.insertBlockMember", $args);
			

			$params->mb_id = $id;
			$params->mb_spammer = 1;
			//ȸ���� ���иӷ� ����
			$model->getDBbyXML("antispam.updateMember", $params);
		}
		*/	
		/* ������Ʈ ������Ʈ(id) */
		function updateBlackListByid($id, $ip, $content, $subject, $spam_type, $spam_score){
			
			if( $id == null ){
				//�Խ�Ʈ ������ ��� ID ������Ʈ�� �߰���Ű�� ����
				return false;
			}
			$args->user_id = $id;
			$args->user_ip = $ip;
			
			
			$args->subject = $subject;
			$args->content = $content;
		
			$args->datetime = date('Y-m-d H:i:s');


			//������ �ִ��� Ȯ��
			$model = new antispamModel();
			$id_param->user_id = $args->user_id;
			$output = $model->getDBbyXML("antispam.getBlackListId", $id_param)->data;

			
			if( ($spam_type=="") && ($spam_score =="") ){
					//����������û�� ���Ѱ�� ī���ø� ����
					$args->spam_type = $output->spam_type;
					$args->spam_score = $output->spam_score;
			}else{
				$args->spam_type = $spam_type;
				$args->spam_score = $spam_score;
			}
			

			if( null == $output ){
				//���� ����Ʈ�� ���� ���� ����
				if( ($spam_type=="") && ($spam_score =="") ){
					//�ʵ带 ���� �����ؾ� �� ��Ȳ���� id���ѿ��� �ɷ������
					$spam = $this->procGetSpamScore($subject.$content);
					$args->spam_type = $spam->type;
					$args->spam_score = $spam->score;
				}
				$args->try_write_spam = '1';
				$model->getDBbyXML("antispam.insertBlackListId", $args);
			}else{
				//���� ����Ʈ�� ����
				$args->try_write_spam = $output->try_write_spam+1;
				$model->getDBbyXML("antispam.updateBlackListId", $args);
			}
			

			return true;
		}

		/* ������Ʈ ������Ʈ(ip) */
		function updateBlackListByIp($id, $ip, $content, $subject, $spam_type, $spam_score){
			$args->user_id = $id;
			$args->user_ip = $ip;

			$args->subject = $subject;
			$args->content = $content;
		
			$args->datetime = date('Y-m-d H:i:s');

			$model = new antispamModel();
			$config = $model->getDBbyXML("antispam.getAdmConfig")->data;
					
		
	
			//������ �ִ��� Ȯ��
			$model = new antispamModel();
			$ip_params->user_ip = $args->user_ip;
			$output = $model->getDBbyXML("antispam.getBlackListIp", $ip_params)->data;

			if( ($spam_type=="") && ($spam_score =="") ){
					//����������û�� ���Ѱ�� ī���ø� ����
					$args->spam_type = $output->spam_type;
					$args->spam_score = $output->spam_score;
			}else{
				$args->spam_type = $spam_type;
				$args->spam_score = $spam_score;
			}
			
			
			if( null == $output ){
				//���� ����Ʈ�� ���� ���� ����
				if( ($spam_type=="") && ($spam_score =="") ){
					//�ʵ带 ���� �����ؾ� �� ��Ȳ���� ip���ѿ��� �ɷ������
					$spam = $this->procGetSpamScore($subject.$content);
					$args->spam_type = $spam->type;
					$args->spam_score = $spam->score;
				}
				$args->try_write_spam = '1';
				$model->getDBbyXML("antispam.insertBlackListIp", $args);
			}else{
				//���� ����Ʈ�� ����
				$args->try_write_spam = $output->try_write_spam+1;
				$model->getDBbyXML("antispam.updateBlackListIp", $args);
			}
			

			return true;
		}

		/* id�� ���и����� �Ǵ�*/
		function isSpammerById($id){
			$obj = new antispamModel();
			$args->user_id = $id;
			$log = $obj->getDBbyXML("antispam.getBlackListId", $args)->data;
			$config = $obj->getDBbyXML("antispam.getAdmConfig")->data;
			
			if( 'Y' == $config->use_block_member ){
				
				$log->datetime = str_replace("-", "", $log->datetime);
				$user_day = (int)strtok($log->datetime, " ");
				
				$today = (int)date('Ymd');

				if( ($today-$user_day) >= $config->date_block_member ){
					//����
					$arg->user_id = $id;
					$obj->getDBbyXML("antispam.deleteBlackId", $arg);
					return false;
				}					
				
				
				//�õ�Ƚ�� �ʰ�
				if( $config->score_block_member <= $log->try_write_spam ){
					return true;
				}
			}
			return false;
		}

		/* ip�� ���и����� �Ǵ� */
		function isSpammerByIp($ip){
			$obj = new antispamModel();
			$args->user_ip = $ip;
			$log = $obj->getDBbyXML("antispam.getBlackListIp", $args)->data;

			$config = $obj->getDBbyXML("antispam.getAdmConfig")->data;
			if( 'Y' == $config->use_block_ip ){

				$log->datetime = str_replace("-", "", $log->datetime);
				$user_day = (int)strtok($log->datetime, " ");
				
				$today = (int)date('Ymd');

				if( ($today-$user_day) >= $config->date_block_member ){
					//����
					$arg->user_ip = $ip;
					$obj->getDBbyXML("antispam.deleteBlackIp", $arg);
					return false;
				}

				if( $config->score_block_ip <= $log->try_write_spam ){
					return true;
				}
			}
			return false;
		}


		/* �������� ���� */
		function procGetSpamScore($request, $exception='#db'){
					
			/* ���ܴܾ� DB���� �������� ��� */
			if( $exception == '#db' ){
				$model = new antispamModel();

				$exception = array();
				$exception = unserialize($model->getDBbyXML('antispam.getAdmConfig')->data->exception_word_list );
			}
			
			
			if( $exception != null ){
				/*	���ܴܾ� ���� */
				foreach( $exception as $exception_word ){
					$request = str_replace($exception_word, "", $request);
				}
			}
			

			$ip = $_SERVER['REMOTE_ADDR'];
			$time = date('Y-m-d H:i:s');



			/* �������� ��û */
			$oReq = new RequestGetSpamScores();
			if( false == $oReq->addContent($request,$ip,$time) ){
				return null;
			}
			/* ���� ���� */
			$output = $oReq->request();
			$result->score = $output->scores->item[0]->score;
			$result->type = $output->scores->item[0]->type;
			return $result;
		}

		function getResult($result){		
			$model = new antispamModel();
			$config = $model->getDBbyXML('antispam.getAdmConfig');
	
			if( $config->data->use_antispam == "Y" ){
				if( $result->score < $config->data->score_antispam ){
					return null;	//�Ϲ�
				}else{
					return $result;	//����
				}
			}else{
				return null;	//�Ϲ�
			}
			
		}

		/* �׽�Ʈ ��� */
		function getTestResult($score, $type,$use_antispam, $score_antispam){

			/* ���� �Ǵ� */	
			if( null == $score ){
				$result->result = -1;	//error
			}
	
			else if( $use_antispam == "N" ){
				$result->result = 0;
			}
			else if( $score <  $score_antispam ){
				$result->result = 0;	//�Ϲݱ�
			}else{
				$result->result = 1;	//���Ա�
			}

			$result->type = $type;
			$result->score = (100 <= $score) ? substr($score,0,3) : substr($score,0,2);
			return $result;
		}


		/* ���� �Խ����� ���� ��� */
		function checkCurrentBoard($search_board, $list){
			/* �Խ����� �ϳ��� ���� ���� ��� */
			if( $list->data == null ){
				return false;	
			}

			if( sizeof($list->data) == 1 ){
				$search_board = $list->data->bo_table;
				return $search_board;
			}

			/* ���� ������ �Խ����� ���� ��� ����ó�� */
			foreach( $list->data as $bd ){
				
				if( $search_board == $bd->bo_table ){
					$exist = 1;
					break;	
				}
			}
			if( $exist != 1 ){
				$bd = $list->data[0];
				$search_board = $list->data[0]->bo_table;
			}
			
			return $search_board;
		}

		/* �Խ����� �� ��� */
		function getDocumentList($search_board, $search_keyword, $search_target, $page, $list_count, $page_count, $order_by, $order){
			if( $page < 1 ){
				$page = 1;
			}
			$args->page = $page;
			//���������� ���̴� ��
			$args->list_count = $list_count;
			//�ѹ��� �������� ������ ��
			$args->page_count = $page_count;
			
			//�˻�����
			$args->wr_id = "";
			$args->wr_content = $search_keyword->search_content;
			$args->mb_id = $search_keyword->search_writer;
			$args->wr_ip = $search_keyword->search_ip;
			$args->spam_score_more = $search_keyword->search_spam_score_more;
			$args->spam_score_less = $search_keyword->search_spam_score_less;
			$args->wr_datetime_more = $search_keyword->search_date_more;
			$args->wr_datetime_less = $search_keyword->search_date_less; 

			if( $args->spam_score_more == 0 && $args->spam_score_less == 100 ){
				$args->spam_score_more = "";
				$spam_score_more = "0";
				$args->spam_score_less = "";
				$spam_score_less = "100";
			}else{
				$spam_score_more = $args->spam_score_more;
				$spam_score_less = $args->spam_score_less;
			}

			//����
			if( $order_by == null ){
				$order_by = 'wr_datetime';
			}
			if( $order == null ){
				$order = 'desc';
			}

			$args->list_order = $order_by;	//���ı���
			$args->desc= $order;	//���Ĺ��

			//���� ���ǿ� �ش� �ϴ� �� ��� ����
			$obj = new antispamModel();

			$this->checkGetXMLQuery($search_board);
			$output = $obj->getDBbyXML("antispam.get$search_board", $args);

			$output->order_by = $order_by;
			$output->order = $order;

			$output->search_content = $search_keyword->search_content;	
			$output->search_writer = $search_keyword->search_writer;		
			$output->search_ip = $search_keyword->search_ip;
			$output->search_spam_score_more = $spam_score_more;
			$output->search_spam_score_less = $spam_score_less;
					
				


			$output->search_date_y_more = $search_keyword->search_date_y_more;
			$output->search_date_y_less = $search_keyword->search_date_y_less;

			$output->search_date_m_more = $search_keyword->search_date_m_more;
			$output->search_date_m_less = $search_keyword->search_date_m_less;

			$output->search_date_d_more = $search_keyword->search_date_d_more;
			$output->search_date_d_less = $search_keyword->search_date_d_less;
			return $output;
		}

		/* ���Ժ������� �� ��� */
		function getStoreList($search_board, $search_keyword, $search_target, $page, $list_count, $page_count, $order_by, $order){
			if( $page < 1 ){
				$page = 1;
			}
			$args->page = $page;
			//���������� ���̴� ��
			$args->list_count = $list_count;
			//�ѹ��� �������� ������ ��
			$args->page_count = $page_count;
			//�˻�����
			$args->wr_id = "";
			$args->wr_content = $search_keyword->search_content;
			$args->mb_id = $search_keyword->search_writer;
			$args->wr_ip = $search_keyword->search_ip;
			$args->spam_score_more = $search_keyword->search_spam_score_more;
			$args->spam_score_less = $search_keyword->search_spam_score_less;
			$args->wr_datetime_more = $search_keyword->search_date_more;
			$args->wr_datetime_less = $search_keyword->search_date_less; 

			//����
			if( $order_by == null ){
				$order_by = 'wr_datetime';
			}
			if( $order == null ){
				$order = 'desc';
			}

			$args->list_order = $order_by;	//���ı���
			$args->desc= $order;	//���Ĺ��

			//���� ���ǿ� �ش� �ϴ� �� ��� ����
			$obj = new antispamModel();
	
				
			$args->board = $search_board;
			$output = $obj->getDBbyXML("antispam.getStore", $args);

			$output->search_target = $search_target;
			//$output->search_keyword = $search_keyword;

			$output->order_by = $order_by;
			$output->order = $order;

			$output->search_content = $search_keyword->search_content;	
			$output->search_writer = $search_keyword->search_writer;		
			$output->search_ip = $search_keyword->search_ip;
			$output->search_spam_score_more = $search_keyword->search_spam_score_more;
			$output->search_spam_score_less = $search_keyword->search_spam_score_less;
					
			$output->search_date_y_more = $search_keyword->search_date_y_more;
			$output->search_date_y_less = $search_keyword->search_date_y_less;

			$output->search_date_m_more = $search_keyword->search_date_m_more;
			$output->search_date_m_less = $search_keyword->search_date_m_less;

			$output->search_date_d_more = $search_keyword->search_date_d_more;
			$output->search_date_d_less = $search_keyword->search_date_d_less;
			return $output;
		}

		/* ���Էα� ��� */
		function getLogList($search_board, $search_keyword, $search_target, $page, $list_count, $page_count, $order_by, $order){
			if( $page < 1 ){
				$page = 1;
			}
			$args->page = $page;
			//���������� ���̴� ��
			$args->list_count = $list_count;
			//�ѹ��� �������� ������ ��
			$args->page_count = $page_count;
			//�˻�����
			$args->wr_id = "";
			$args->wr_content = $search_keyword->search_content;
			$args->mb_id = $search_keyword->search_writer;
			$args->wr_ip = $search_keyword->search_ip;
			$args->spam_score_more = $search_keyword->search_spam_score_more;
			$args->spam_score_less = $search_keyword->search_spam_score_less;
			$args->wr_datetime_more = $search_keyword->search_date_more;
			$args->wr_datetime_less = $search_keyword->search_date_less; 
			//Ÿ��
			$args->spam_type = $search_keyword->search_spam_type; 

			//����
			if( $order_by == null ){
				$order_by = 'wr_datetime';
			}
			if( $order == null ){
				$order = 'desc';
			}

			$args->list_order = $order_by;	//���ı���
			$args->desc= $order;	//���Ĺ��

			//���� ���ǿ� �ش� �ϴ� �� ��� ����
			$obj = new antispamModel();
	
				
			//$args->board = $search_board;
			$output = $obj->getDBbyXML("antispam.getLog", $args);

			$output->order_by = $order_by;
			$output->order = $order;

			$output->search_content = $search_keyword->search_content;	
			$output->search_writer = $search_keyword->search_writer;		
			$output->search_ip = $search_keyword->search_ip;
			$output->search_spam_score_more = $search_keyword->search_spam_score_more;
			$output->search_spam_score_less = $search_keyword->search_spam_score_less;
					
			$output->search_date_y_more = $search_keyword->search_date_y_more;
			$output->search_date_y_less = $search_keyword->search_date_y_less;

			$output->search_date_m_more = $search_keyword->search_date_m_more;
			$output->search_date_m_less = $search_keyword->search_date_m_less;

			$output->search_date_d_more = $search_keyword->search_date_d_more;
			$output->search_date_d_less = $search_keyword->search_date_d_less;

			if( "" == $search_keyword->search_spam_type ){
				$output->spam_type = "all";
			}

			return $output;
		}


		/* ������Ʈ ��� */
		function getBlackList($search_board, $search_keyword, $search_target, $page, $list_count, $page_count){
			if( $page < 1 ){
				$page = 1;
			}

			$args->page = $page;
			//���������� ���̴� ��
			$args->list_count = $list_count;
			//�ѹ��� �������� ������ ��
			$args->page_count = $page_count;
			

			$obj = new antispamModel();
			$config = $obj->getDBbyXML("antispam.getAdmConfig", $args)->data;
			
			if( $search_board == "getBlackListIdDisp" ){
				if( 'Y' == $config->use_block_member ){
					$args->try_write_spam = $config->score_block_member;
				}else{
					$args->try_write_spam = PHP_INT_MAX;
				}	
			}else if( $search_board == "getBlackListIpDisp" ){
				if( 'Y' == $config->use_block_ip ){
					$args->try_write_spam = $config->score_block_ip;
				}else{
					$args->try_write_spam = PHP_INT_MAX;
				}
			}else{
				$args->try_write_spam = PHP_INT_MAX;
			}

			
			$args->list_order = "datetime";	//���ı���
			$args->desc = 'desc';	//���Ĺ��

			//���� ���ǿ� �ش� �ϴ� �� ��� ����

			$output = $obj->getDBbyXML("antispam.$search_board", $args);

			//$output->search_target = $search_target;
			//$output->search_keyword = $search_keyword;
			return $output;
		}
	
		/* ���� ���� ���������� ���Լ��� ��¥�� �����ϰ� ���� �� ��� */
		function setExistingDocSpamScore($search_board, $content, $id, $spam_config_exception_word){
			$result = $this->procGetSpamScore($content);
			if( null == $result->score ){
				return null;	//error
			}else{
				$args->spam_score = $result->score;	//test
			}
			$args->spam_config_exception_word = $spam_config_exception_word;
			$args->wr_id = $id;
			
			$args->spam_type = $result->type;


			
			$obj = new antispamModel();
			$this->checkUpdateXMLQuery($search_board);
			$obj->getDBbyXML("antispam.update$search_board", $args);
			
			return $result;
		}

		/* ���������Կ� ���� �ֱ� */
		function insertContentDocumentlist($content, $board){
			$obj = new antispamModel();
			return $obj->getDBbyXML("antispam.insert$board", $content);
		}

		/* ���Ժ����Կ� ���� �ֱ� */
		function insertContentInStore($content, $board){
			$content->board = $board;
			$obj = new antispamModel();
			return $obj->getDBbyXML("antispam.insertStore", $content);
		}

		/* ���Էα� ���� �ֱ� */
		function insertContentInLog($score, $type, $content, $id, $date, $ip){
			$args->spam_score=$score;
			$args->spam_type=$type;
			$args->wr_content=$content;
			$args->mb_id=$id;
			$args->wr_datetime=$date;
			$args->wr_ip=$ip;


			$obj = new antispamModel();
			return $obj->getDBbyXML("antispam.insertLog", $args);
		}
		
		/* �ش�Խ����� �ش�id �� ���� */
		function deleteContent($search_board, $id){
			$obj = new antispamModel();
			$arg->wr_id = $id;
					
			$this->checkDeleteXMLQuery( $search_board );
			$obj->getDBbyXML("antispam.delete$search_board", $arg);
			return $content;
		}

		/* ���Ժ������� �ش�id �� ���� */
		function deleteStoreContent($search_board, $id){
			$obj = new antispamModel();
			$args->wr_id = $id;
			$args->board = $search_board;
			
			$obj->getDBbyXML("antispam.deleteStore", $args);
			return $content;
		}


	
		/* �ش� �Խ��ǿ� �ش��ϴ� ���� ������ �ִ��� Ȯ��(������ ����) */
		function checkGetXMLQuery($board){
	
			$model = new antispamModel();

			$xml_file = sprintf('queries/get%s.xml',$board );
			if(!file_exists($xml_file)){
			
				//�÷��߰�
				$model->addColumn("write_".$board, "spam_score", "number", "11");
				$model->addColumn("write_".$board, "spam_type", "varchar", "10");
				$model->addColumn("write_".$board, "spam_config_exception_word", "text", "0");
				
				/* ���� ���� */
				$buff = "
				<query id=\"get".$board."\" action=\"select\">
						<tables>
							<table name=\"write_".$board."\" />
						</tables>
						<columns>
							<column name=\"*\" />
						</columns>
						<conditions>
							<condition operation=\"equal\" column=\"wr_id\" var=\"wr_id\" pipe=\"and\" />
							<condition operation=\"like\" column=\"wr_content\" var=\"wr_content\" pipe=\"and\" />
							<condition operation=\"like\" column=\"mb_id\" var=\"mb_id\" pipe=\"and\" />
							<condition operation=\"more\" column=\"wr_datetime\" var=\"wr_datetime_more\" pipe=\"and\" />
							<condition operation=\"less\" column=\"wr_datetime\" var=\"wr_datetime_less\" pipe=\"and\" />
							<condition operation=\"like\" column=\"wr_ip\" var=\"wr_ip\" pipe=\"and\" />
							<condition operation=\"more\" column=\"spam_score\" var=\"spam_score_more\" pipe=\"and\" />
							<condition operation=\"less\" column=\"spam_score\" var=\"spam_score_less\" pipe=\"and\" />
						</conditions>
					<navigation>
						<index var=\"list_order\" default=\"wr_datetime\" order=\"desc\" />
						<list_count var=\"list_count\" default=\"3\" />
					    <page_count var=\"page_count\" default=\"3\" />
					    <page var=\"page\" default=\"1\" />
					</navigation>
				</query>";
				$mode="w";
				$file_name = "queries/get".$board.".xml";

				$mode = strtolower($mode);
		        if($mode != "a") $mode = "w";
			    $fp = fopen($file_name,$mode);
				fwrite($fp, $buff);
				fclose($fp);
				@chmod($file_name, 0644);
			}

		}

		/* �ش� �Խ��ǿ� �ش��ϴ� ���� ������ �ִ��� Ȯ��(������ ����) */
		function checkUpdateXMLQuery($board){
			$xml_file = sprintf('queries/update%s.xml',$board );
			if(!file_exists($xml_file)){
				/* ���� ���� */
				$buff =
				"<query id=\"update".$board."\" action=\"update\">
					<tables>
						<table name=\"write_".$board."\" />
					</tables>
					<columns>
						<column name=\"spam_score\" var=\"spam_score\" default='0' />
						<column name=\"spam_type\" var=\"spam_type\" default='null' />
						<column name=\"spam_config_exception_word\" var=\"spam_config_exception_word\" default='' />
					</columns>
					<conditions>
						<condition operation=\"equal\" column=\"wr_id\" var=\"wr_id\" />	
					</conditions>
				</query>";
				$mode="w";
				$file_name = "queries/update".$board.".xml";

				$mode = strtolower($mode);
		        if($mode != "a") $mode = "w";
			    $fp = fopen($file_name,$mode);
				fwrite($fp, $buff);
				fclose($fp);
				@chmod($file_name, 0644);
			}

		}
		
		/* �ش� �Խ��ǿ� �ش��ϴ� ���� ������ �ִ��� Ȯ��(������ ����) */
		function checkDeleteXMLQuery($board){
			$xml_file = sprintf('queries/delete%s.xml',$board );
			if(!file_exists($xml_file)){
				/* ���� ���� */
				$buff =
				"<query id=\"delete".$board."\" action=\"delete\">
					<tables>
						<table name=\"write_".$board."\" />
					</tables>
					<conditions>
						<condition operation=\"equal\" column=\"wr_id\" var=\"wr_id\" />	
					</conditions>
				</query>";
				$mode="w";
				$file_name = "queries/delete".$board.".xml";

				$mode = strtolower($mode);
		        if($mode != "a") $mode = "w";
			    $fp = fopen($file_name,$mode);
				fwrite($fp, $buff);
				fclose($fp);
				@chmod($file_name, 0644);
			}
		}

		/* �ش� �Խ��ǿ� �ش��ϴ� ���� ������ �ִ��� Ȯ��(������ ����) */
		function checkInsertXMLQuery($board){
			$xml_file = sprintf('queries/insert%s.xml',$board );
			if(!file_exists($xml_file)){
				/* ���� ���� */
				$buff =
				"<query id=\"insert".$board."\" action=\"insert\">
					<tables>
						<table name=\"write_".$board."\" />
					</tables>
					<columns>
						<column name=\"wr_id\" var=\"wr_id\" />
						<column name=\"wr_num\" var=\"wr_num\" />
						<column name=\"wr_reply\" var=\"wr_reply\" />
						<column name=\"wr_parent\" var=\"wr_parent\" />
						<column name=\"wr_is_comment\" var=\"wr_is_comment\" />
						<column name=\"wr_comment\" var=\"wr_comment\" />
						<column name=\"wr_comment_reply\" var=\"wr_comment_reply\" />
						<column name=\"ca_name\" var=\"ca_name\" />
						<column name=\"wr_option\" var=\"wr_option\" />
						<column name=\"wr_subject\" var=\"wr_subject\" />
						<column name=\"wr_content\" var=\"wr_content\" />
						<column name=\"wr_link1\" var=\"wr_link1\" />
						<column name=\"wr_link2\" var=\"wr_link2\" />
						<column name=\"wr_link1_hit\" var=\"wr_link1_hit\" />
						<column name=\"wr_link2_hit\" var=\"wr_link2_hit\" />
						<column name=\"wr_trackback\" var=\"wr_trackback\" />
						<column name=\"wr_hit\" var=\"wr_hit\" />
						<column name=\"wr_good\" var=\"wr_good\" />
						<column name=\"wr_nogood\" var=\"wr_nogood\" />
						<column name=\"mb_id\" var=\"mb_id\" />
						<column name=\"wr_password\" var=\"wr_password\" />
						<column name=\"wr_name\" var=\"wr_name\" />
						<column name=\"wr_email\" var=\"wr_email\" />
						<column name=\"wr_homepage\" var=\"wr_homepage\" />
						<column name=\"wr_datetime\" var=\"wr_datetime\" />
						<column name=\"wr_last\" var=\"wr_last\" />
						<column name=\"wr_ip\" var=\"wr_ip\" />
						<column name=\"wr_1\" var=\"wr_1\" />
						<column name=\"wr_2\" var=\"wr_2\" />
						<column name=\"wr_3\" var=\"wr_3\" />
						<column name=\"wr_4\" var=\"wr_4\" />
						<column name=\"wr_5\" var=\"wr_5\" />
						<column name=\"wr_6\" var=\"wr_6\" />
						<column name=\"wr_7\" var=\"wr_7\" />
						<column name=\"wr_8\" var=\"wr_8\" />
						<column name=\"wr_9\" var=\"wr_9\" />
						<column name=\"wr_10\" var=\"wr_10\" />
						<column name=\"spam_score\" var=\"spam_score\" />
						<column name=\"spam_type\" var=\"spam_type\" />
						<column name=\"spam_config_exception_word\" var=\"spam_config_exception_word\" />
					</columns>
				</query>";
				$mode="w";
				$file_name = "queries/insert".$board.".xml";

				$mode = strtolower($mode);
		        if($mode != "a") $mode = "w";
			    $fp = fopen($file_name,$mode);
				fwrite($fp, $buff);
				fclose($fp);
				@chmod($file_name, 0644);
			}
		}
	}
?>
