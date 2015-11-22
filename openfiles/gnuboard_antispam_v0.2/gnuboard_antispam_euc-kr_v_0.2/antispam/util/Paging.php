<?php

// ����¡ ����� �Լ�
function paging($page, $total_page, $page_scale, $total_count, $ext = '')
{
    // 1. ��ü ������ ���
		

    // 2. ����¡�� ����� ���� �ʱ�ȭ
    $paging_str = "";

    // 3. ó�� ������ ��ũ �����
//    if ($page > 1) {
        $paging_str .= "<a href='".$_SERVER[PHP_SELF]."?page=1&".$ext."'>ó��</a>";
//    }

    // 4. ����¡�� ǥ�õ� ���� ������ ���ϱ�
    $start_page = ( (ceil( $page / $page_scale ) - 1) * $page_scale ) + 1;

    // 5. ����¡�� ǥ�õ� ������ ������ ���ϱ�
    $end_page = $start_page + $page_scale - 1;
    if ($end_page >= $total_page) $end_page = $total_page;

    // 6. ���� ����¡ �������� ���� ��ũ �����
    if ($start_page > 1){
        $paging_str .= " &nbsp;<a href='".$_SERVER[PHP_SELF]."?page=".($start_page - 1)."&".$ext."'>...</a>";
    }

    // 7. �������� ��� �κ� ��ũ �����
    if ($total_page >= 1) {
        for ($i=$start_page;$i<=$end_page;$i++) {
            // ���� �������� �ƴϸ� ��ũ �ɱ�
            if ($page != $i){
                $paging_str .= " &nbsp;<a href='".$_SERVER[PHP_SELF]."?page=".$i."&".$ext."'><span>$i</span></a>";
            // ������������ ���� ǥ���ϱ�
            }else{
                $paging_str .= " &nbsp;<b>$i</b> ";
            }
        }
    }

    // 8. ���� ����¡ �������� ���� ��ũ �����
    if ($total_page > $end_page){
        $paging_str .= " &nbsp;<a href='".$_SERVER[PHP_SELF]."?page=".($end_page + 1)."&".$ext."'>...</a>";
    }

    // 9. ������ ������ ��ũ �����
  //  if ($page < $total_page) {
        $paging_str .= " &nbsp;<a href='".$_SERVER[PHP_SELF]."?page=".$total_page."&".$ext."'>�ǳ�</a>";
  //  }

    return $paging_str;
}
?>

