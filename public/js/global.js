
$(window).load(function() {


	function callbackFacebook() {

	}


	function bindEvents() {

		$('#connect').click(function(event) {			

			wsh.exec('oauth -provider facebook', function(json) {				
				$('body').append(json.view);
				syncSetCallback(callbackFacebook);
			});

			event.preventDefault();
		});


		// Slider's commands
		$(".slider-nav").delegate("a", "click", function() {
			
			// Next slide
			if( $(this).hasClass("next") ) {
				$(".js-slider").data("µSlide").slideTo("next");
			// Previous slide
			} else if( $(this).hasClass("prev") ) {
				$(".js-slider").data("µSlide").slideTo("previous");
			}

		})

	}


	(function init() {

		// All user's events
		bindEvents();

		// Initialize the slide
		$(".js-slider").µSlide({child_class: ".slide"});

	})();


});
