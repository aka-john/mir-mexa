<p>
	<a href="#myModal" role="button" data-toggle="modal" class="btn btn-primary"><span class="icon icon-white icon-plus-sign"></span> Добавить перенаправление</a>
</p>

<table class="table table-condensed table-striped table-hover table-bordered">
	<thead>
		<tr>
			<th>id</th>
			<th>code</th>
			<th>source</th>
			<th>target</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<? while ($r = $modx->db->getRow($redirects)): ?>
			<tr>
				<td><?=$r['redirect_id']?></td>
				<td>
					<select name="edit[code]" class="span1">
						<option value="301">301</option>
						<option value="302">302</option>
					</select>
				</td>
				<td><input type="text" name="edit[source]" value="<?=$r['redirect_source']?>" class="span5" /></td>
				<td><input type="text" name="edit[target]" value="<?=$r['redirect_target']?>" class="span5" /></td>
				<td>
					<input type="hidden" name="edit[redirect]" value="<?=$r['redirect_id']?>">
					<a href="<?=$url?>&get=redirect" title="Обновить" class="btn btn-small btn-success update"><span class="icon icon-white icon-repeat"></span></a>
					<a href="<?=$url?>&get=redirect&delete=<?=$r['redirect_id']?>" title="Удалить" class="btn btn-small btn-danger"><span class="icon icon-white icon-remove-sign"></span></a>
				</td>
			</tr>
		<? endwhile ?>
	</tbody>

</table>

<div id="myModal" class="modal hide fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
  <div class="modal-header">
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
    <h3 id="myModalLabel">Добавить перенаправление</h3>
  </div>
  <form action="<?=$url?>&get=redirect" method="post">
  	<div class="modal-body">
		<table class="table table-condensed table-bordered table-hover table-striped">
			<tr>
				<td>Код перенаправления</td>
				<td>
				    <select name="add[code]" class="span3">
						<option value="301">301 - permanent</option>
						<option value="302">302 - temporary</option>
						<option value="404">404 - not found</option>
					</select>
				</td>
			</tr>
			<tr>
				<td>Перенаправить с</td>
				<td><input type="text" name="add[source]" class="span4" value="" /></td>
			</tr>
			<tr>
				<td>на</td>
				<td><input type="text" name="add[target]" class="span4" value="" /></td>
			</tr>
		</table>
  	</div>
  	<div class="modal-footer">
  		<a href="#" class="btn btn-warning" data-dismiss="modal" aria-hidden="true">Отмена</a>
  	  <input type="submit" value="Добавить перенаправление" class="btn btn-success">
  	</div>
  </form>
</div>