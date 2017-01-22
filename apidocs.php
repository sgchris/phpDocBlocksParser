<?php

// get the search query from the URL
$query = $_GET['query'] ?? '';

$docsParserFolder = __DIR__;
$docsDbFilePath = $docsParserFolder . '/docs.db';
if (!file_exists($docsDbFilePath) || !is_readable($docsDbFilePath)) {
	exit ('The documentation DB file ('.$docsDbFilePath.') is inaccessible');
}

// get the results from the DB
require_once $docsParserFolder . '/libs/db.php';
$results = DB::getInstance()->getDocs($query);

?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<title>API-DOCs Zend Server</title>
		
		<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
		<style>
		.app-name-header {
			margin: 0;
			padding: 0;
		}
		.empty-placeholder {
			color: #EEE;
			font-size: 100px;
			line-height: 150px;
		}
		.method-name-get {
			color: green;
		}
		.method-name-post {
			color: #C11;
		}
		</style>
	</head>
	<body>
		<div class="container-fluid">
			<div class="well well-small">
				<div class="row">
					<div class="col-md-4 col-xs-12">
						<form class="form-inline" method="GET" action="">
							<div class="input-group" style="width: 100%;">
								<input type="text" name="query" 
									value="<?php echo htmlentities($query, ENT_QUOTES); ?>" 
									class="form-control input-group" 
									placeholder="API method name..." autofocus 
								/>
								<div class="input-group-btn">
									<button type="submit" class="btn btn-default form-control">
										<i class="glyphicon glyphicon-search"></i>
									</button>
								</div>
							</div>
						</form>
					</div>
					<div class="col-md-8 col-xs-12">
						<h1 class="app-name-header">API Documentation <small>Documentation for Zend Server web APIs</small></h1>
					</div>
				</div>
			</div>
			<div class="row">
				<div class="col-md-3 col-xs-12">
					<ul id="search-results" class="nav nav-pills nav-stacked">
						<?php foreach ($results as $rec) { ?>
						<li>
							<a href="#" api-name="<?php echo htmlentities($rec['name'], ENT_QUOTES); ?>" title="<?php echo htmlentities($rec['name'], ENT_QUOTES); ?> from &quot;gui/<?php echo htmlentities($rec['file'], ENT_QUOTES); ?>&quot;">
								<?php echo $rec['name']; ?>
								<small><?php echo $rec['file']; ?></small>
							</a>
						</li>
						<?php } ?>
					</ul>
				</div>
				<div id="doc-item-data" class="col-md-9 col-xs-12">
					<span class="empty-placeholder">API details...</span>
				</div>
			</div>
		</div>
	
		<script src="https://code.jquery.com/jquery-2.2.4.min.js"   integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44="   crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
		<script>
		var results = <?php echo json_encode($results); ?>;
		
		$(function() {
			$('ul#search-results').on('click', 'a[api-name]', function(e) {
				// cancel the default anchor click
				e.preventDefault();
				
				// clear the content section
				$('#doc-item-data').empty();
				
				var $el = $(e.target);
				
				// check that the element is "<a>"
				while ($el && $el.get(0).tagName !== 'A') $el = $el.parent();
				if (!$el) return;
				
				var apiName = $el.attr('api-name');
				
				var apiData = null;
				results.every(function(item) {
					if (item.name == apiName) {
						apiData = item;
						return false; // break;
					}
					
					return true;
				});
				
				if (apiData) {
					var docItemHtml = '<h2><span class="method-name method-name-' + apiData.parsed_docblock.method.toLowerCase() + '">' + apiData.parsed_docblock.method + '</span> ' + apiName + '</h2>';
					docItemHtml+= '<p>' + apiData.parsed_docblock.brief + '</p>';
					docItemHtml+= '<hr />';
					docItemHtml+= '<dl class="dl-horizontal">';
					Object.keys(apiData.parsed_docblock).forEach(function(apiDocKey) {
						// skip the "api" key
						if (['api', 'return', 'method', 'brief'].includes(apiDocKey)) {
							return;
						}
						
						docItemHtml+= '<dt>' + apiDocKey + '</dt>';
						docItemHtml+= '<dd>';
						// display the parameters
						if (apiDocKey == 'params' || apiDocKey == 'param') {
							docItemHtml+= '<table class="table table-striped table-bordered"><tbody>';
							if (apiDocKey == 'param') {
								docItemHtml+= '<tr><td>' + apiData.parsed_docblock[apiDocKey] + '</td></tr>';
							} else if (apiDocKey == 'params') {
								apiData.parsed_docblock[apiDocKey].forEach(function(param) {
									docItemHtml+= '<tr><td>' + param + '</td></tr>';
								});
							}
							docItemHtml+= '</tbody></table>';
						} else {
							if (apiDocKey == 'response') {
								docItemHtml+= '<pre>';
							}
							docItemHtml+= apiData.parsed_docblock[apiDocKey].replace(/\n+/g, '<br />').replace(/\t/g, '<span style="width:30px;display:inline-block;"></span>');
							if (apiDocKey == 'response') {
								docItemHtml+= '</pre>';
							}
						}
						docItemHtml+= '</dd>';
					});
					docItemHtml+= '</dl>';
					
					$('#doc-item-data').html(docItemHtml);
				}
			});
		});
		</script>
	</body>
</html>