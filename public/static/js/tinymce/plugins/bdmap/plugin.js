tinymce.PluginManager.add('bdmap', function(editor, url) {
	var pluginName='插入百度地图';
	var baseURL=tinymce.baseURL;
	var iframe1 = baseURL+'/plugins/bdmap/map.html';
	var bdmap_width = function (editor) {
		return editor.getParam('bdmap_width', 560);
    };
    var bdmap_height = function (editor) {
		return editor.getParam('bdmap_height', 362);
    };
	window.tinymceLng='';
	window.tinymceLat='';
	var openDialog = function() {
		return editor.windowManager.openUrl({
			title: pluginName,
			size: 'large',
			url:iframe1,
			buttons: [
				{
					type: 'cancel',
					text: 'Close'
				},
				{
					type: 'custom',
					text: 'Save',
					name: 'save',
					primary: true
				},
			],
			onAction: function (api, details) {
				switch (details.name) {
					case 'save':
						html='<iframe src="'+baseURL+'/plugins/bdmap/bd.html?center='+tinymceLng+'%2C'+tinymceLat+'&zoom=14&width='+(bdmap_width(editor)-2)+'&height='+(bdmap_height(editor)-2)+'" frameborder="0" style="width:'+bdmap_width(editor)+'px;height:'+bdmap_height(editor)+'px;">';
						editor.insertContent(html);
						api.close();
						break;
					default:
						break;
				}
				
			}
		});
	};

	editor.ui.registry.getAll().icons.bdmap || editor.ui.registry.addIcon('bdmap','<?xml version="1.0" encoding="UTF-8"?><svg width="20" height="20" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg"><rect width="48" height="48" fill="white" fill-opacity="0.01"/><path d="M9.85786 32.7574C6.23858 33.8432 4 35.3432 4 37C4 40.3137 12.9543 43 24 43V43C35.0457 43 44 40.3137 44 37C44 35.3432 41.7614 33.8432 38.1421 32.7574" stroke="#333" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/><path d="M24 35C24 35 37 26.504 37 16.6818C37 9.67784 31.1797 4 24 4C16.8203 4 11 9.67784 11 16.6818C11 26.504 24 35 24 35Z" fill="none" stroke="#333" stroke-width="4" stroke-linejoin="round"/><path d="M24 22C26.7614 22 29 19.7614 29 17C29 14.2386 26.7614 12 24 12C21.2386 12 19 14.2386 19 17C19 19.7614 21.2386 22 24 22Z" fill="none" stroke="#333" stroke-width="4" stroke-linejoin="round"/></svg>');
	
	editor.ui.registry.addButton('bdmap', {
		icon: 'bdmap',
        tooltip: pluginName,
		onAction: function() {
			openDialog();
		}
	});


	editor.ui.registry.addMenuItem('bdmap', {
		text: pluginName,
		onAction: function() {
			openDialog();
		}
	});
	return {
		getMetadata: function() {
			return  {
				name: pluginName,
				url: "http://tinymce.ax-z.cn/more-plugins/bdmap.php",
			};
		}
	};
});
