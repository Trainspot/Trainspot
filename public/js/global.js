
$(window).load(function() {



	function bindEvents() {
		
		$(".datepicker" ).datepicker();

		$('#connect').click(function(event) {			

			wsh.exec('oauth -provider facebook', function(json) {				
				$('body').append(json.view);
				syncSetCallback(callbackFacebook);
			});

			event.preventDefault();
		});


		// Slider's commands
		$(".slider-nav").delegate("a,li", "click", function() {
			
			// Next slide
			if( $(this).hasClass("next") ) {
				$(".js-slider").data("µSlide").slideTo("next");
			// Previous slide
			} else if( $(this).hasClass("prev") ) {
				$(".js-slider").data("µSlide").slideTo("previous");

			// Bullet na
			} else if( $(this).hasClass("bullet") ) {
				$(".js-slider").data("µSlide").slideTo( $(this).index() );
			}

		});

		// Slider callback
		$(".js-slider").bind("after-slide", function(object, index) {

			// Active bullet
			$(".slider-nav .bullet").removeClass("active").eq(index).addClass("active");
			
			// Next and prev buttons
			if(index == 0) $(".slider-nav .prev").hide();
			else $(".slider-nav .prev").show();

			if(index == 3) $(".slider-nav .next").hide();
			else $(".slider-nav .next").show();
		});


		$("form.js-setup").delegate(":input[name=regulier]", "change", function() {
			
			if( $(this).is(":checked") ) {
				$("form.js-setup .js-frequence").show();
				$("form.js-setup .js-date").hide();
			} else {
				$("form.js-setup .js-frequence").hide();
				$("form.js-setup .js-date").show();				
			}
		});


		$(".js-interest").delegate(".domain", "change", function() {

			if( $(this).val() == -1 ) {		
				$(this).parents(".span6").find(".content").hide();
			} else {								
				// show the content input
				$(this)
					.parents(".span6")
					.find(".content")
						.show()
						.find(":input[type=text]")
							.attr("placeholder", $(this).find(":selected").data("placeholder") )
							.keydown(function(e) {
								if (e.keyCode == 13)
								{
									if ($('#your-proposal-input').val() != '' && $('#your-need-input').val() != '')
									{
										$.ajax({
											url: '/user/interest',
											type: 'post',
											data: 'titleproposal=' + $('#your-proposal').val() + '&proposal=' + $('#your-proposal-input').val() + '&titleneed=' + $('#your-need').val() + '&need=' + $('#your-need-input').val(),
											success:function(data) {
												$('#proposal-container, #need-container').fadeOut('fast');
												window.location.href = '/user/interest';
											}
										});
									}
									else
									{
										alert('Pour chaque choses que vous cherchez, vous devez proposer autres choses en contre partie')
									}
								return false;
								}
							})
			}

		});
	}


	(function init() {

		// All user's events
		bindEvents();

		// Initialize the slide
		$(".js-slider").µSlide({child_class: ".slide"});

	})();


});
