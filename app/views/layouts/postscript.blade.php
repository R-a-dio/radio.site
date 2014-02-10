<!-- Bootstrap core JavaScript
	================================================== -->
<script src="//code.jquery.com/jquery-2.0.3.min.js"></script>
<script src="//netdna.bootstrapcdn.com/bootstrap/3.0.3/js/bootstrap.min.js"></script>

<!-- jQuery ScrollTo Plugin -->
<script src="/js/jquery.scrollto.js"></script>
<script src="/js/jquery.timeago.js"></script>
<!-- History.js -->
<script src="/js/jquery.history.js"></script>
<script src="/js/furigana.js"></script>
<script src="/js/jquery.jplayer.min.js"></script>
<script>swfpath = "/js/Jplayer.swf";</script>

<script>
	$(function() {
		$.timeago.settings.allowFuture = true;
		$.timeago.settings.strings.minutes = "%d mins";
		$.timeago.settings.strings.minute = "less than a min";
		$.timeago.settings.strings.seconds = "less than a min";
		$.timeago.settings.strings.suffixFromNow = null;
		$.timeago.settings.strings.prefixFromNow = "in";
		
		function supports_html5_storage() {
			try {
				return 'localStorage' in window && window['localStorage'] !== null;
			} catch (e) {
				return false;
			}
		}

		if (supports_html5_storage()) {

			if (!localStorage["volume"]) {
				localStorage["volume"] = 80;
			}
			$("#volume").val(parseInt(localStorage["volume"]));

		} else {
			$("#volume").val(80);
		}

		$.fn.serializeObject = function() {
			var o = {};
			var a = this.serializeArray();
			$.each(a, function() {
				if (o[this.name] !== undefined) {
					if (!o[this.name].push) {
						o[this.name] = [o[this.name]];
					}
					o[this.name].push(this.value || '');
				} else {
					o[this.name] = this.value || '';
				}
			});
			return o;
		};
		window.player_source_set = false;
		function handlers() {
			$("time.timeago").timeago();

			// Ajaxify
			$('a.ajax-navigation, ul.pagination>li>a').click(function(event) {

				// Continue as normal for cmd clicks etc
				if (event.which == 2 || event.metaKey) { return true; }

				event.preventDefault();

				History.pushState(null, $(this).text(), $(this).attr("href"));
				
				return false;
			});

			$('form.ajax-search').submit(function(event) {

				// Continue as normal for cmd clicks etc
				if (event.which == 2 || event.metaKey) { return true; }

				event.preventDefault();

				var arr = $(this).serializeObject();
				
				History.pushState(null, $(this).text(), $(this).attr("action") + "/" + arr.q);
				
				return false;
			});


			if (History.getState().url.replace(History.getRootUrl(), "/") == "/") {
				$("#stream").jPlayer({
					ready: function () {
						$("#stream-play").text("{{{ trans("stream.play") }}}").removeClass("disabled");
						$("#stream-play").click(function() {
							if(window.player_source_set == false) {
								$("#stream").jPlayer("setMedia", {'mp3': location.protocol + '//stream.r-a-d.io/main.mp3'});
								window.player_source_set = true;
								$("#stream").jPlayer("play");
								$(this).hide();
								$("#volume-image").hide();
								$("#stream-stop").show();
								$("#volume-control").show();
							}
						});
						$("#stream-stop").click(function() {
							$("#stream").jPlayer("pause");
							$(this).hide();
							$("#stream-play").show();
						});
					},
					pause: function() {
						$("#stream").jPlayer("clearMedia");
						$("#volume-control").hide();
						$("#volume-image").show();
						window.player_source_set = false;
						$("#stream-play").click(function() {
							if(window.player_source_set == false) {
								$("#stream").jPlayer("setMedia", {'mp3': location.protocol + '//stream.r-a-d.io/main.mp3'});
								window.player_source_set = true;
								$("#stream").jPlayer("play");
							}
						});
					},
					volume: Math.pow(($("#volume").val() / 100), 2.0),
					supplied: "mp3",
					swfPath: swfpath
				});
			}
			
			$("#volume").change(function (event) {
				if (supports_html5_storage()) {
					localStorage["volume"] = $(this).val();
				}
				$("#stream").jPlayer("volume", Math.pow(($(this).val() / 100), 2.0));
			});

			$("textarea.comment-input").keyup(function() {
				var $object = $(this);
				var id = $object.attr("data-id");
				var value = $object.val();
				$("#char-count-" + id).text(500 - value.length);
			});

		}
		handlers();

		$(window).on("statechange", function(event) {
			var state = History.getState();
			var root = History.getRootUrl();

			var uri = state.url.replace(root, "/");

			var $next = $("[data-uri='" + uri + "']");

			// handle li.active
			var active = uri.split("/")[1];

			$(".navbar .active").removeClass("active");
			var $active = $(".navbar a[href='/" + active + "']");

			if ($active.hasClass("drop")) {
				$active = $active.closest("li.dropdown");
			} else {
				$active = $active.closest("li");
			}
			
			$active.addClass("active");


			if ($next.length) {
				// we've been to this page before.
				var $current = $("#radio-container > .current");

				$current.removeClass("current").hide();
				$next.addClass("current").show();
			} else {
				// we need to fetch it and append it

				$.ajax({
					url: uri,
					type: "GET",
					dataType: "html",
					success: function(data) {
						if (data) {
							var $section = $(data);

							var $current = $("#radio-container > .current");

							$current.removeClass("current").hide();
							$section.appendTo($("#radio-container")).addClass("current").show();

							handlers();

						}
					}
				})
			}

		});

		



		///////////////////////////////////////////////////////////////////////
		// HERE BE DRAGONS
		// MOVE TO MAIN.JS WHEN DONE
		///////////////////////////////////////////////////////////////////////

		window.radio = {
			counter: 0.0,
			sync_seconds: 0,
			update_progress: 0.0,
			update_progress_inc: 0.0,
			update_old_progress: 0.0,
			current_pos: 0,
			current_len: 0,
			afk: 1
		};

		String.prototype.format = function() {
			var args = arguments;
			return this.replace(/{(\d+)}/g, function(match, number) { 
				return typeof args[number] != 'undefined'
				? args[number]
				: match
				;
			});
		};

		function setDjImage(image) {
			$("#dj-image").attr("src", "//r-a-d.io/res/img/dj/" + image);
		}

		function setDJ(dj) {
			$("#dj-name").text(dj.djname);
			setDjImage(dj.djimage);
		}

		function nowPlaying(np) {
			$("#np").text(np);
		}

		function setListeners(listeners) {
			$("#listeners").text(listeners);
		}

		function updateLastPlayed(lp) {
			var $lp = $("#lastplayed .last-played");
			var count = 0;
			$lp.each(function() {
				var lastplayed = lp[count];
				$(this).find(".col-md-4").html(lastplayed.time);
				$(this).find(".col-md-8").text(lastplayed.meta);
				count++;
			});
		}

		function updateQueue(q) {
			var $q = $("#queue .queue");
			var count = 0;
			$q.each(function() {
				var queue = q[count];
				$(this).find(".col-md-4").html(queue.time);
				$(this).find(".col-md-8").text(queue.meta);

				if (queue.type > 0) {
					var html = $(this).find(".col-md-8").html();
					$(this).find(".col-md-8").html("<b>" + html + "</b>");
				}

				count++;
			});
		}

		function updatePeriodic() {
			$.ajax({
				method: 'get',
				url: "/api",
				dataType: 'json',
				success: function (resp) {
					nowPlaying(resp.main.np);
					setListeners(resp.main.listeners);

					if (radio.afk != resp.main.isafkstream) {
						radio.afk = resp.main.isafkstream;

						if (radio.afk) {
							$("#queue").show();
							$("#dj-mode").hide();
						} else {
							$("#queue").hide();
							$("#dj-mode").show();
						}
					}
					
					setDJ(resp.main.dj);
					updateLastPlayed(resp.main.lp);
					updateQueue(resp.main.queue);
					parseProgress(resp.main.start_time, resp.main.end_time, resp.main.current);
					$("time.timeago").timeago();
				}
			});
		}

		function parseProgress(start, end, cur) {
			if (end != 0) {
				radio.cur_time = Math.round(new Date().getTime() / 1000.0);
				radio.sync_seconds = radio.cur_time - cur;
				end += radio.sync_seconds;
				start += radio.sync_seconds;
				radio.temp_update_progress = 0;
				radio.duration = end - start;
				radio.position = radio.cur_time - start;
				radio.update_progress = 100 / radio.duration * radio.position;
				radio.update_progress_inc = 100 / radio.duration * 0.5;
				radio.current_pos = radio.position;
				radio.current_len = radio.duration;
			}
			else {
				radio.update_progress = false;
			}
		}


		function secondsToReadable(seconds) {
			var hours = Math.floor(seconds / 3600);
			var mins = Math.floor((seconds % 3600) / 60);
			var secs = Math.floor((seconds % 3600) % 60);
			return (
				hours > 0 ? +
				(hours < 10 ? '0' : '') +
				hours + ':' : '') +
				(mins < 10 ? '0' :'') +
				mins + ':' +
				(secs < 10 ? '0' : '') + secs;
		}

		function applyProgress() {
			if (radio.update_progress) {
				if ((radio.update_progress < radio.update_old_progress) || (radio.update_old_progress == false)) {
					radio.update_progress += radio.update_progress_inc;
					$("#progress .progress-bar").removeClass("progress-bar-danger");
					$("#progress .progress-bar").css({width: "{0}%".format(radio.update_progress)});
				}
				else {
					radio.update_progress += radio.update_progress_inc;
					$("#progress .progress-bar").removeClass("progress-bar-danger");
					$("#progress .progress-bar").css({width: "{0}%".format(radio.update_progress)});
				}
				// Fill the duration counter
				$("#progress-current").text(secondsToReadable(radio.current_pos));
				$("#progress-length").text(secondsToReadable(radio.current_len));
			}
			else {
				$("#progress .progress-bar").addClass("progress-bar-danger");
				$("#progress .progress-bar").css({width: "100%"})
				// Empty the duration counter
				$("#progress-current").empty();
				$("#progress-length").empty();
			}
			radio.update_old_progress = radio.update_progress;
		}

		function periodic() {
			radio.counter += 0.5;
			radio.current_pos += 0.5;
			applyProgress();
			if (radio.counter >= 10.0) {
				updatePeriodic();
				radio.counter = 0;
			}
		}

		updatePeriodic();
		setInterval(periodic, 500);



	});
</script>
