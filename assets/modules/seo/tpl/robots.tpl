<form action="<?=$url?>&get=robots" method="post">
	<p><b>Файл robots.txt</b> – это текстовый файл, находящийся в корневой директории сайта, в котором записываются специальные инструкции для поисковых роботов. Эти инструкции могут запрещать к индексации некоторые разделы или страницы на сайте, указывать на правильное «зеркалирование» домена, рекомендовать поисковому роботу соблюдать определенный временной интервал между скачиванием документов с сервера и т.д.</p>
	<textarea name="post" class="span12" cols="60" rows="10"><?=file_get_contents($_SERVER['DOCUMENT_ROOT']."/robots.txt")?></textarea>
	<br>
	<input type="submit" value="Обновить файл robots.txt" class="btn btn-large btn-primary">
</form>
<?=implode("",$modx->invokeEvent("OnSnipFormRender"));?>