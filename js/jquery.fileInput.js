// requires jquery, underscore
try {
	(function($) {
		if (!$) return false;
		defaults = {
			templates: {
				'label': '<label for="<%=id%>"><%=text%><input name="<%=hidden%>" type="checkbox" id="<%=id%>" value="<%=value%>" \/><\/label>',
				'file':  '<span><input type="file" name="<%=name%>" \/><\/span>'
			},
			phrases: {
				'edit': 'Редактировать карту'
			},
			css: {
				node: 'fi_node',
				hidden: 'fi_hidden'
			},
			errors: [],
			
			inputName: 'mapfile',
			hiddenInputName: 'mapchange',
			hiddenInputValue: '1'
		};
		$.fn.extend({
			fileInput: function(params) {
				params = params || {};
				return this.each(function() {
					$.extend(this, {
						// получить идентификатор для label:
						get_input_id: function() {
							try {
								var count = 0;
								var id='';
								do {
									count++;
									if (count > 100) return 'file_input';
									id = 'fi_' + Math.floor(Math.random()*10000000000);
									if (!$('#' + id).length) return id;
								} while (true);
							} catch (e) {this.errors.push({func:'get_input_id',err:e});return 'file_input'};
						},
						// отобразить:
						render: function() {
							try {
								var that    = this;
								var css     = this['css'];
								var phrases = this['phrases'];
								$(this).addClass(css['node']);
								// формируем данные для шаблонов:
								var data = {
									'id':     this.get_input_id(),
									'text':   phrases['edit'],
									'name':   this['inputName'],
									'hidden': this['hiddenInputName'],
									'value':  this['hiddenInputValue']
								};
								// формируем html-код:
								var templates = this['templates'];
								var labelNode = $(_.template(templates['label'])(data));
								var fileNode  = $(_.template(templates['file'])(data));
								$(this).append(labelNode, fileNode);
								// обработчик:
								$('input[type=checkbox]', labelNode).bind('change', function() {
									if (this.checked)
										$('span', that).show();	
									else
										$('span', that).hide();
								});
								return true;
							} catch (e) {this.errors.push({func:'render',err:e});return false};
						},
						// инициализация:
						init: function() {
							try {
								$.extend(true, this, defaults, params);
								this.render();
								return true;
							} catch (e) {this.errors.push({func:'init',err:e});return false};
						}
					});
					// инициализировать объект:
					this.init(params);
				});
			}
		});
	})(jQuery)
} catch (e) {};