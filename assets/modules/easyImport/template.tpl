<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  
	<head>
		<link rel="stylesheet" type="text/css" href="media/style/[+manager_theme+]/style.css" /> 
	<!--	<script type="text/javascript" src="../assets/js/jquery-1.7.1.min.js"></script>		
		<script>
		$(document).ready(function() {
	
	// Tab Functionality
	$("h2.tab").click(function(){
		var sCurrentId = $(this).attr("id");
		$("h2.tab").removeClass("selected");
		$("div.tab-page").css({"display" : "none"});
		$("#tab-" + sCurrentId).css({"display" : "block"});
		$(this).addClass("selected");
	});
});
		</script>-->
	</head>
  
	<body style="background-color:#EEEEEE">
    
     <!-- <h1>Импорт-Экспорт</h1>
		<div id="actions">
      <ul class="actionButtons">
        <li id="Button1"><a onclick="document.location.href='index.php?a=106';" href="#"><img src="media/style/MODxCarbon/images/icons/stop.png">Закрыть модуль</a></li>
      </ul>
    </div>-->
		<div class="sectionBody">
    
      

			<div id="modulePane" class="dynamic-tab-pane-control tab-pane">
				<div class="tab-row">
					<h2 id="page1" class="tab selected"><span>Импорт Скидок</span></h2>
					<!--<a href="index.php?a=112&id=10"><h2 id="page2" class="tab"><span>Комплектации</span></h2></a>
					<a href="index.php?a=112&id=13"><h2 id="page3" class="tab "><span>Модели</span></h2></a>-->
				</div>
        
				<div id="tab-page1" class="tab-page" style="display:block;">


				    <p><strong>Загрузить данные о скидках из csv</strong>
				    	<br/><a href="/assets/modules/easyImport/import2.csv">Пример файла</a></p>
					
					<form method="POST" action="[+moduleurl+]action=import" enctype="multipart/form-data">
						<!--select id="cat" name="parent">[+cat+]</select-->
						<br/><br/>
			            <input type="file" name="csv">
			            <br/><br/>
			            <input type="submit" value='импортировать'>
			        </form> 
        			<big>перед импортом рекомендую делать бекап данных что б не было неожиданностей. сделать его можно <a href="index.php?a=93">тут</a> последняя вкладка</big>
				
				</div> 

				[+totalimp+]
				
			</div>
		</div>	
    </body>
  </html>