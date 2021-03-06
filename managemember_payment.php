<?php
	require_once("./include/session.php");
	require_once("./include/function.php");
	check_permission("MEM_PAYMENT");
	
	if(!$_POST)
	{
		//load up the payment page
		if($_GET)
		{
			$sql = "SELECT * FROM tbl_youth WHERE youthID =$_GET[youthid]";
			$result = mysql_query($sql);
			$row = mysql_fetch_array($result);
		}
		else
		{
			echo "Error: No user selected.";
			exit();
		}
	}
	else
	{
		//check for previous payment
		$sql = "
				SELECT
					*
				FROM `tbl_youth` 
				LEFT JOIN (
					SELECT 
						registrationid,
						youthid, 
						fee,
						paid,
						MAX(registration_date) AS registration_date 
					FROM `tbl_youth_registration`
					GROUP BY youthid
					) AS table2
				ON tbl_youth.youthid = table2.youthid 
				WHERE 
					registration_date + INTERVAL 1 YEAR  > now() AND 
					tbl_youth.youthid = $_POST[youthid]";
		$result = mysql_query($sql);
		
		//cancel payment if they already paid
		if(mysql_num_rows($result) > 0)
		{
			echo "Has already Paid.";
			exit();
		}

		//register payment	
		$sql = "
			INSERT INTO 
				`tbl_youth_registration` 
				(
					youthID,
					registration_date,
					fee,
					paid
				)
			VALUES 
				(
					'$_POST[youthid]',
					'" . dateformat2($_POST[date]) . "',
					'$_POST[fee]',
					'$_POST[paid]'
				);";
		if(!mysql_query($sql))
		{
			die("Error: " . mysql_error());
		}
		log_event("MEM_PAYMENT",$_SESSION["userid"],$_POST["youthid"]);
		header("location:managemember_data.php?youthid=" . $_POST["youthid"]);
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
	<link rel="stylesheet" type="text/css" href="css/default.css" />
	<script type="text/javascript" language="JavaScript">
	</script>
	<style type="text/css">
	</style>
</head>
<body>
	<div id="wrapper">
	<?php require_once("./include/header.php"); ?>
	<?php require_once("./include/nav.php"); ?>
	<div id="content">
		<h2>Manage Members - <?=$row["f_name"] . " " . $row["l_name"];?> - Payment</h2>
		<form action="<?=$_SERVER["PHP_SELF"];?>" method="post">
			<input type="submit" name="submit" value="Save">
			<a href="managemember_data.php?youthid=<?=$_GET["youthid"];?>"><input type="button" name="cancel" value="Cancel"></a>
			Youth ID:<input type="text" name="youthid" value="<?=$_GET["youthid"];?>">
			Date:<input type="text" name="date" value="<?=date("d/m/Y");?>">
			Fee($):<input type="text" name="fee" value="10">
			Paid:<input type="checkbox" name="paid" value="1" checked="checked" />
		</form>
	</div>
	<?php require_once("./include/footer.php"); ?>
	</div>
</body>
</html>