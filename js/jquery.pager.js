// плагин предназначен для отрисовки панели перехода между страницами
// requires jquery
try {
	(function($) {
		defaults = {
			errors: [],
			phrases: {
				'begin': '<<',
				'end': '>>',
				'left': '<',
				'right': '>'
			},
			css: {
				node: 'pager_node',
				item: 'pager_item',
				selected_item: 'pager_item_selected',
				positionInput: 'pager_position',
				segmentInput: 'pager_segment'
			},
			html: {
				inputName: 'restrictions[limit][]'
			},
			
			handler: null,
			
			ajax: {
				async: true,
				cache: false,
				url: 'ajax.php',
				dataType: 'text',
				timeout: 5000,
				type: 'POST'
			},
			
			totalNumber: 0,						// общее число элементов
			number: 0,							// текущий элемент
			segment: 20,						// величина отрезка
			form: null,							// ссылка на форму
			
			currentPosition: 0,					// текущая позиция
			displayButtonsNumber: 3				// количество кнопок, отображаемых слева и справа от центральной
		};
		
		$.fn.extend({
			pager: function(params) {
				params = params || {};
				return this.each(function() {
					this.get_positions_number = function() {
						try {
							return Math.ceil(this.totalNumber / this.segment);
						} catch (e) {
							this.errors.push({func:'get_positions_number',err:e});
							return 0;
						};
					};
					
					this.get_current_position = function() {
						try {
							return Math.floor(this.number / this.segment);
						} catch (e) {
							this.errors.push({func:'get_current_position',err:e});
							return 0;
						};
					};
					
					this.get_current_number = function() {
						try {
							return this.get_current_position() * this.segment;
						} catch (e) {
							this.errors.push({func:'get_current_number',err:e});
							return 0;
						};
					};
					// получить данные для формирования скрытых инпутов:
					this.get_inputs_data = function() {
						try {
							return [this.get_current_number(), this.segment];
						} catch (e) {
							this.errors.push({func:'get_inputs_data',err:e});
							return [0, this.segment];
						};
					};
					// добавить скрытые инпуты в форму:
					this.append_inputs = function() {
						try {
							var form = this.form;
							var css  = this.css;
							var html = this.html;
							var data = this.get_inputs_data();
							
							$('.'+css.positionInput+', .' + css.segmentInput, form).remove();
							var positionInput = $('<input>').attr({
								type: 'hidden',
								name: html.inputName,
								value: data[0],
								'class': css.positionInput
							});
							var segmentInput = $('<input>').attr({
								type: 'hidden',
								name: html.inputName,
								value: data[1],
								'class': css.segmentInput
							});
							$(form).append(positionInput).append(segmentInput);
							return true;
						} catch (e) {
							this.errors.push({func:'append_inputs',err:e});
							return false;
						};
					};
					// отправить форму:
					this.submit = function() {
						try {
							$(this.form).submit();
							return true;
						} catch (e) {
							this.errors.push({func:'submit',err:e});
							return false;
						};
					};
					// установить текущую позицию:
					this.set_position = function(index, init) {
						try {
							var css  = this.css;
							var form = this.form;
							var positionsNumber = this.get_positions_number();
							if (positionsNumber <= index) index = positionsNumber - 1;
							if (index < 0) index = 0;
							// сохраняем текущую позицию:
							var buffPosition = this.currentPosition;
							this.currentPosition = index;
							this.number = index * this.segment;
							// устанавливаем значения скрытых полей формы:
							this.append_inputs();
							// выполняем обработчик, если таковой имеется и текущая позиция менялась:
							if (this.handler && buffPosition != index) this.handler(index);
							// если задана форма - отправляем её, иначе отрисовываем список заново:
							if (init)	this.render();
							else if (buffPosition != index) {
								if (this.form)	this.submit();
								else			this.render();
							};
							return true;
						} catch (e) {
							this.errors.push({func:'set_position',err:e});
							return false;
						};
					};
					// в начало:
					this.set_home = function() {
						try {
							return this.set_position(0);
						} catch (e) {
							this.errors.push({func:'set_home',err:e});
							return false;
						};
					};
					// в конец:
					this.set_end = function() {
						try {
							return this.set_position(this.get_positions_number()-1);
						} catch (e) {
							this.errors.push({func:'set_end',err:e});
							return false;
						};
					};
					// влево:
					this.to_left = function() {
						try {
							var currentPosition = this.get_current_position();
							if (currentPosition > 0)
								this.set_position(currentPosition - 1);
							return true;
						} catch (e) {
							this.errors.push({func:'to_left',err:e});
							return false;
						};
					};
					// вправо:
					this.to_right = function() {
						try {
							var currentPosition = this.get_current_position();
							var positionsNumber = this.get_positions_number();
							if (currentPosition < positionsNumber-1)
								this.set_position(currentPosition + 1);
							return true;
						} catch (e) {
							this.errors.push({func:'to_right',err:e});
							return false;
						};
					};
					// отрисовка управляющих кнопок:
					this.render = function() {
						try {
							var css     = this.css;
							var form    = this.form;
							var phrases = this.phrases;
							var that    = this;
							var positionsNumber = this.get_positions_number();
							var currentPosition = this.get_current_position();
							var displayButNum   = this.displayButtonsNumber;
							// формируем кнопки:
							$(this).html('').addClass(css.node);
							// добавляем навигационную кнопку "до начала":
							if (currentPosition > 1) {
								var toBeginNode = $('<div>').addClass(css.item).html(phrases.begin).click(function() {
									that.set_home();
								});
								$(this).append(toBeginNode);
							};
							// добавляем навигационную кнопку "влево":
							if (currentPosition > 0) {
								var toLeftNode  = $('<div>').addClass(css.item).html(phrases.left).click(function() {
									that.to_left();
								});
								$(this).append(toLeftNode);
							};
							// добавляем цифровые навигационные кнопки:
							for (var i=currentPosition-displayButNum; i < currentPosition+displayButNum+1; i++)
							if (i >= 0 && i < positionsNumber) {
								(function() {
									var j = i;
									var itemNode = $('<div>').addClass(css.item).html(i+1).click(function() {
										that.set_position(j);
									});
									if (i == currentPosition) itemNode.addClass(css.selected_item);
									$(that).append(itemNode);
								})();
							};
							// добавляем навигационную кнопку "вправо":
							if (currentPosition < positionsNumber-1) {
								var toRightNode  = $('<div>').addClass(css.item).html(phrases.right).click(function() {
									that.to_right();
								});
								$(this).append(toRightNode);
							};
							// добавляем навигационную кнопку "до конца":
							if (currentPosition < positionsNumber-2) {
								var toEndNode = $('<div>').addClass(css.item).html(phrases.end).click(function() {
									that.set_end();
								});
								$(this).append(toEndNode);
							};
							return true;
						} catch (e) {
							this.errors.push({func:'render',err:e});
							return false;
						};
					};
					// инициализация:
					this.init = function(params) {
						try {
							$.extend(true, this, defaults, params);
							this.set_position(this.get_current_position(), true);
							return true;
						} catch (e) {
							this.errors.push({func:'init',err:e});
							return false;
						};
					};
					
					this.init(params);
				});
			}
		});
	})(jQuery)
} catch (e) {};