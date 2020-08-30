<?php
$settings['display'] = 'datatable';
$settings['fields'] = array(
	'name' => array(
		'caption' => 'Место',
		'type' => 'text'
	),
	'phone' => array(
		'caption' => 'Телефоны',
		'type' => 'textarea'
	),
	'time' => array(
		'caption' => 'График',
		'type' => 'textarea'
	)
);
$settings['columns'] = array(
	array(
		'caption' => 'Настройки',
		'fieldname' => 'name'
	)
);
$settings['form'] = array(
	array(
		'caption' => 'Настройки',
		'content' => array(
			'name' => array(
			),
			'phone' => array(
			),
			'time' => array(
			)
		)
	)
);

$settings['templates'] = array(
	'outerTpl' => '

[+wrapper+]
',
	'rowTpl' => '
[+img_bg+]
[+row.number+]
[+iteration+]
[+title+]
[+row.class+]
<br/>'
);
$settings['configuration'] = array(
	'enablePaste' => FALSE,
	'csvseparator' => ','
);
$settings['templatesTest'] = array(
	'outerTpl' => '<ul>[+wrapper+]</ul>',
	'rowTpl' => '<li>[+text+], [+image+], [+thumb+], [+textarea+], [+date+], [+dropdown+], [+listbox+], [+listbox-multiple+], [+checkbox+], [+option+]</li>'
		)
?>