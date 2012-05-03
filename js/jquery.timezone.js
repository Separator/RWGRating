try {
	(function($) {
		var timezoneDefaults = {
			css: {
				'select': 'timezone_select'
			},
			errors: [],
			
			timezones: {
				'(GMT -12:00) Эневеток, Кваджалейн': -720,
				'(GMT -11:00) Остров Мидуэй, Самоа': -660,
				'(GMT -10:00) Гавайи': -600,
				'(GMT -9:00) Аляска': -540,
				'(GMT -8:00) Тихоокеанское время (США и Канада), Тихуана': -480,
				'(GMT -7:00) Горное время (США и Канада), Аризона': -420,
				'(GMT -6:00) Центральное время (США и Канада), Мехико': -360,
				'(GMT -5:00) Восточное время (США и Канада), Богота, Лима': -300,
				'(GMT -4:30) Каракас': -270,
				'(GMT -4:00) Атлантическое время (Канада), Ла Пас': -240,
				'(GMT -3:30) Ньюфаундленд': -210,
				'(GMT -3:00) Бразилия, Буэнос-Айрес, Джорджтаун': -180,
				'(GMT -2:00) Среднеатлантическое время': -120,
				'(GMT -1:00) Азорские острова, острова Зелёного Мыса': -60,
				'(GMT) Дублин, Лондон, Лиссабон, Касабланка, Эдинбург': 0,
				'(GMT +1:00) Брюссель, Копенгаген, Мадрид, Париж, Берлин': 60,
				'(GMT +2:00) Афины, Киев, Минск, Бухарест, Рига, Таллин': 120,
				'(GMT +3:00) Москва, Санкт-Петербург, Волгоград': 180,
				'(GMT +3:30) Тегеран': 210,
				'(GMT +4:00) Абу-Даби, Баку, Тбилиси, Ереван': 240,
				'(GMT +4:30) Кабул': 270,
				'(GMT +5:00) Екатеринбург, Исламабад, Карачи, Ташкент': 300,
				'(GMT +5:30) Мумбай, Колката, Ченнаи, Нью-Дели': 330,
				'(GMT +5:45) Катманду': 345,
				'(GMT +6:00) Омск, Новосибирск, Алма-Ата, Астана': 360,
				'(GMT +6:30) Янгон, Кокосовые острова': 390,
				'(GMT +7:00) Красноярск, Норильск, Бангкок, Ханой, Джакарта': 420,
				'(GMT +8:00) Иркутск, Пекин, Перт, Сингапур, Гонконг': 480,
				'(GMT +9:00) Якутск, Токио, Сеул, Осака, Саппоро': 540,
				'(GMT +9:30) Аделаида, Дарвин': 570,
				'(GMT +10:00) Владивосток, Восточная Австралия, Гуам': 600,
				'(GMT +11:00) Магадан, Сахалин, Соломоновы Острова': 660,
				'(GMT +12:00) Камчатка, Окленд, Уэллингтон, Фиджи': 720
			},
			
			name: 'timezone',
			
			get_offset: function() {
				try {
					return (new Date()).getTimezoneOffset()*-1;
				} catch (e) {this.errors.push({func:'get_offset',err:e}); return 0};
			},
			
			render: function() {
				try {
					var timezones = this.timezones;
					var timezoneOffset = this.get_offset();
					var selectNode = $('<select>').attr('name', this.name).addClass(this.css.select);
					for (var j in timezones) {
						var optionNode = $('<option>').html(j).attr('value', timezones[j]);
						if (timezoneOffset == timezones[j])
							optionNode.attr('selected', 'selected');
						selectNode.append(optionNode);
					};
					$(this).html('').append(selectNode);
					return true;
				} catch (e) {this.errors.push({func:'render',err:e}); return false};
				return true;
			},
			
			init: function() {
				try {
					this.render();
				} catch (e) {this.errors.push({func:'init',err:e}); return false};
				return true;
			}
		};
		
		$.fn.timezoneOffset = function(params) {
			return this.each(function() {
				$.extend(true, this, timezoneDefaults, params);
				this.init();
			});
		};
	})(jQuery);
} catch (e) {};
