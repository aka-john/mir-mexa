<form action="<?=$url?>&get=counters" method="post">
	<table class="table table-bordered table-condensed table-striped">
		<tr>
			<td width="200px">
				<b>Google Analytics</b><br>
				<small>[(seo_ga)]</small>
			</td>
			<td><textarea name="seo_ga" class="span10" cols="30" rows="5"><?=$modx->config['seo_ga']?></textarea></td>
		</tr>
		<tr>
			<td>
				<b>Яндекс Метрика</b><br>
				<small>[(seo_ya)]</small>
			</td>
			<td><textarea name="seo_ya" class="span10" cols="30" rows="5"><?=$modx->config['seo_ya']?></textarea></td>
		</tr>
	</table>
	<input type="submit" value="Сохранить" class="btn btn-large btn-primary">
</form>