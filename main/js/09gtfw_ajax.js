/**
 * GtfwAjax, wraps AdvAjax
 */
var GtfwAjaxBase = Base
		.extend({
			replaceContent : function(el_id, content) {
				try {
					var el = document.getElementById(el_id);
				} catch (exp) {
					alert('Security issue: Unable to get element object('
							+ el_id + ')');
				}

				if (el) {
					var applyXHRJs = this.applyXHRJs;
					var newcontent;
					// $(el).fadeOut(200,function(){

					// Multi Windows support
					if (el_id.indexOf("popup") !== -1) {
						var re = /dest_popup-subcontent/gi;
						var str = content;
						newcontent = str.replace(re, "dest_" + el_id);
					} else {
						newcontent = content;
					}

					el.innerHTML = newcontent;

					// behaviour support, reapply rules for new content
					if (window.Behaviour)
						Behaviour.apply();
					// hides baloon
					if (window.BaloonManager)
						BaloonManager.hideBaloon();
					// aplly JS, since it won't be automatically exec'd
					applyXHRJs(el_id);

					// $(el).fadeIn(200);
					// });

					if (window.afterReplaceContent) {
						afterReplaceContent();
					}
				} else
					alert('Cannot found: <div id="' + el_id + '">');
			},
			replaceContentWithUrl : function(el_id, url, headers) {
				if (!headers)
					headers = 'html';

				var self = this;
				self.el_id = el_id;
				// patch : support multi windows
				eval('var this_callback = function(obj) { self.replaceContent("'
						+ self.el_id
						+ '", obj.responseText); if(self.el_id.indexOf("popup") !== -1) { $("#'
						+ self.el_id
						+ ' .dest_popup-subcontent").removeClass("dest_popup-subcontent").addClass("dest_'
						+ self.el_id + '");}}');
				this.xhrGet(url, this_callback, headers);
			},
			postFormReplace : function(el_id, dest_id) {
				var el = document.getElementById(el_id);
				var self = this;
				self.dest_id = dest_id;
				eval('var this_callback = function(obj) { self.replaceContent("'
						+ self.dest_id + '", obj.responseText); }');

				advAJAX
						.submit(
								document.getElementById(el_id),
								{
									onInitialization : function() {
										// loading status support
										if (XhrStatus)
											XhrStatus.start(
													'loading-box-active', 50);
									},
									mimeType : 'text/plain',
									onSuccess : function(obj) {
										// loading status support
										if (XhrStatus)
											XhrStatus.stop(
													"loading-box-active", 100);
										this_callback(obj);
									},
									onError : function(obj) {
										if (XhrStatus)
											XhrStatus.stop(
													"loading-box-active", 100);
										// unauthorized or cookie has been
										// expired
										if (obj.status == 401) {
											var redir = obj.xmlHttpRequest
													.getResponseHeader('Location');
											if (redir) {
												document.location.href = redir;
											} else {
												alert('Sorry, we encountered an HTTP 401 Unauthorized access!');
											}
										} else if (obj.status == 0) {
											jsWindowModal(
													'content',
													'Lost Connection',
													'Lost Connection to Server',
													'lost_connection');
										}
									},
									headers : {
										'X-GtfwXhrRequestSignature' : new Date()
												.toString()
									}
								});
			},
			postForm : function(el_id, callback, headers) {
				var el = document.getElementById(el_id);
				if (!headers)
					headers = {
						'X-GtfwModuleType' : 'html'
					};
				else
					headers = {
						'X-GtfwModuleType' : headers
					}

				headers['X-GtfwXhrRequestSignature'] = new Date().toString();

				// History.saveState(src)

				if (callback) {
					advAJAX
							.submit(
									document.getElementById(el_id),
									{
										onInitialization : function() {
											// loading status support
											if (XhrStatus)
												XhrStatus.start(
														'loading-box-active',
														50);
										},
										headers : headers,
										mimeType : 'text/plain',
										onSuccess : function(obj) {
											// loading status support
											if (XhrStatus)
												XhrStatus.stop(
														"loading-box-active",
														100);
											eval(callback + '(obj);');
										},
										onError : function(obj) {
											if (XhrStatus)
												XhrStatus.stop(
														"loading-box-active",
														100);
											// unauthorized or cookie has been
											// expired
											if (obj.status == 401) {
												var redir = obj.xmlHttpRequest
														.getResponseHeader('Location');
												if (redir) {
													document.location.href = redir;
												} else {
													alert('Sorry, we encountered an HTTP 401 Unauthorized access!');
												}
											} else if (obj.status == 0) {
												jsWindowModal(
														'content',
														'Lost Connection',
														'Lost Connection to Server',
														'lost_connection');
											}
										}
									});
				} else {
					advAJAX
							.submit(
									document.getElementById(el_id),
									{
										onInitialization : function() {
											// loading status support
											if (XhrStatus)
												XhrStatus.start(
														'loading-box-active',
														50);
										},
										headers : headers,
										mimeType : 'text/plain',
										onSuccess : function(obj) {
											// loading status support
											if (XhrStatus)
												XhrStatus.stop(
														"loading-box-active",
														100);
											GtfwAjax.defCallback(obj);
										},
										onError : function(obj) {
											if (XhrStatus)
												XhrStatus.stop(
														"loading-box-active",
														100);
											// unauthorized or cookie has been
											// expired
											if (obj.status == 401) {
												var redir = obj.xmlHttpRequest
														.getResponseHeader('Location');
												if (redir) {
													document.location.href = redir;
												} else {
													alert('Sorry, we encountered an HTTP 401 Unauthorized access!');
												}
											} else if (obj.status == 0) {
												jsWindowModal(
														'content',
														'Lost Connection',
														'Lost Connection to Server',
														'lost_connection');
											}
										}
									});
				}
			},
			xhrGet : function(src, the_callback, str_headers) {
				GtfwAjax.__xhrGet(src, the_callback, str_headers);
				// if (typeof History == 'object')
				// History.pushStack(this, this.__xhrGet, [src, the_callback,
				// str_headers], src);
			},
			__xhrGet : function(src, the_callback, str_headers) {
				var headers = new Object();

				if (!str_headers)
					headers = {
						'X-GtfwModuleType' : 'html'
					};
				else
					headers = {
						'X-GtfwModuleType' : str_headers
					};

				headers['X-GtfwXhrRequestSignature'] = new Date().toString();

				advAJAX
						.get({
							url : src,
							onInitialization : function() {
								// loading status support
								if (XhrStatus)
									XhrStatus.start('loading-box-active', 50);
							},
							headers : headers,
							mimeType : 'text/plain',
							onSuccess : function(obj) {
								the_callback(obj);
								// loading status support
								if (XhrStatus)
									XhrStatus.stop("loading-box-active", 100);

							},
							onError : function(obj) {
								if (XhrStatus)
									XhrStatus.stop("loading-box-active", 100);

								// unauthorized or cookie has been expired
								if (obj.status == 401) {
									var redir = obj.xmlHttpRequest
											.getResponseHeader('Location');
									if (redir) {
										document.location.href = redir;
									} else {
										alert('Sorry, we encountered an HTTP 401 Unauthorized access!');
									}
								} else if (obj.status == 0) {
									jsWindowModal('content', 'Lost Connection',
											'Lost Connection to Server',
											'lost_connection');
								}
							}
						});
			},
			xhrPost : function(src, the_callback, str_headers) {

				this.__xhrPost(src, the_callback, str_headers);
				// if (typeof History == 'object')
				// History.pushStack(this, this.__xhrPost, [src, the_callback,
				// str_headers], src);
			},
			__xhrPost : function(src, the_callback, str_headers) {

				var headers = new Object();
				if (!str_headers)
					headers = {
						'X-GtfwModuleType' : 'html'
					};
				else
					headers = {
						'X-GtfwModuleType' : str_headers
					};

				headers['X-GtfwXhrRequestSignature'] = new Date().toString();

				obj = new Object();

				obj = {
					parameters : data,
					url : src,
					onInitialization : function() {
						// loading status support
						if (XhrStatus)
							XhrStatus.start('loading-box-active', 50);
					},
					headers : headers,
					mimeType : 'text/plain',
					onSuccess : function(obj) {
						the_callback(obj);
						// loading status support
						if (XhrStatus)
							XhrStatus.stop("loading-box-active", 100);
					},
					onError : function(obj) {
						if (XhrStatus)
							XhrStatus.stop("loading-box-active", 100);
						// unauthorized or cookie has been expired
						if (obj.status == 401) {
							var redir = obj.xmlHttpRequest
									.getResponseHeader('Location');
							if (redir) {
								document.location.href = redir;
							} else {
								alert('Sorry, we encountered an HTTP 401 Unauthorized access!');
							}
						} else if (obj.status == 0) {
							jsWindowModal('content', 'Lost Connection',
									'Lost Connection to Server',
									'lost_connection');
						}

					}
				};

				/*
				 * if (data != null) { for ( var item in data) { obj[item] =
				 * data[item]; } }
				 */
				advAJAX.post(obj);
			},
			applyXHRJs : function(el_id) {
				var bSaf = (navigator.userAgent.indexOf('Safari') != -1);
				var bOpera = (navigator.userAgent.indexOf('Opera') != -1);
				var bMoz = (navigator.appName == 'Netscape');
				var node = document.getElementById(el_id);
				var st = node.getElementsByTagName('SCRIPT');
				var strExec;

				/*
				 * http://microformats.org/wiki/rest/ahah with eval tips from:
				 * http://service.zimki.com/user/blog/tomi/2006/08/07/javascript-eval
				 * /// NOTICE: /// Always use "/ * * /" comment instead of "//"
				 * within loaded html/js since it will be eval'd in one line.
				 */

				for ( var i = 0; i < st.length; i++) {
					if (bSaf) {
						strExec = st[i].innerHTML;
					} else if (bOpera) {
						strExec = st[i].text;
					} else if (bMoz) {
						strExec = st[i].textContent;
					} else {
						strExec = st[i].text;
					}
					try {
						self.eval(strExec);
					} catch (e) {
						alert(e
								+ "\nMake sure you have use \\* *\\ style of comment instead of \\\\");
						alert('code was: ' + strExec);
					}
				}
			},
			defCallback : function(obj) {
				try {
					eval("result = " + obj.responseText + ";");
				} catch (e) {
					alert(obj.responseText);
					var result = new Array;
				}
				if (result['exec']) {
					eval(result['exec'] + ";");
				}
			},
			loadUrl : function(theurl, the_callback, headers) {
				if (!headers)
					headers = {
						'X-GtfwModuleType' : 'html'
					};
				else
					headers = {
						'X-GtfwModuleType' : headers
					};

				headers['X-GtfwXhrRequestSignature'] = new Date().toString();

				// History.saveState(src);

				if (the_callback) {
					advAJAX
							.get({
								url : theurl,
								onInitialization : function() {
									// loading status support
									if (XhrStatus)
										XhrStatus.start('loading-box-active',
												50);
								},
								headers : headers,
								mimeType : 'text/plain',
								onSuccess : function(obj) {
									// loading status support
									if (XhrStatus)
										XhrStatus.stop("loading-box-active",
												100);
									the_callback(obj);
								},
								onError : function(obj) {
									if (XhrStatus)
										XhrStatus.stop("loading-box-active",
												100);
									// unauthorized or cookie has been expired
									if (obj.status == 401) {
										var redir = obj.xmlHttpRequest
												.getResponseHeader('Location');
										if (redir) {
											document.location.href = redir;
										} else {
											alert('Sorry, we encountered an HTTP 401 Unauthorized access!');
										}
									} else if (obj.status == 0) {
										jsWindowModal('content',
												'Lost Connection',
												'Lost Connection to Server',
												'lost_connection');
									}
								}
							});
				} else {
					advAJAX
							.get({
								url : theurl,
								onInitialization : function() {
									// loading status support
									if (XhrStatus)
										XhrStatus.start('loading-box-active',
												50);
								},
								headers : headers,
								mimeType : 'text/plain',
								onSuccess : function(obj) {
									// loading status support
									if (XhrStatus)
										XhrStatus.stop("loading-box-active",
												100);
									GtfwAjax.defCallback(obj);
								},
								onError : function(obj) {
									if (XhrStatus)
										XhrStatus.stop("loading-box-active",
												100);

									// unauthorized or cookie has been expired
									if (obj.status == 401) {
										var redir = obj.xmlHttpRequest
												.getResponseHeader('Location');
										if (redir) {
											document.location.href = redir;
										} else {
											alert('Sorry, we encountered an HTTP 401 Unauthorized access!');
										}
									} else if (obj.status == 0) {
										jsWindowModal('content',
												'Lost Connection',
												'Lost Connection to Server',
												'lost_connection');
									}
								}
							});
				}
			},
			waitOn : function(elm) {
				var el = document.getElementById(elm);
				var org_src = document.getElementById("loading-box-active");
				if (el) {
					el.innerHTML = '<div id="loading-box-active"/>&nbsp;</div><b>WAITING...</b>'
							+ org_src.innerHTML; // / FIXME: hardcoded wait
					// signal
				}
			}
		});

var GtfwAjax = new GtfwAjaxBase();

/**
 * XhrStatus, shows XHR status
 */
var XhrStatus = {
	taskCount : 0,
	isFadingOut : false,
	isFadingIn : false,
	fadeStage : 0,
	increaseTask : function() {
		this.taskCount += 1;
	},
	decreaseTask : function() {
		this.taskCount -= 1;
	},
	start : function(el_id, delay) {
		var body = document.body, html = document.documentElement;

		var height = Math.max(body.scrollHeight, body.offsetHeight,
				html.clientHeight, html.scrollHeight, html.offsetHeight);
		var Width = Math.max(body.scrollWidth, body.offsetWidth,
				html.clientWidth, html.scrollWidth, html.offsetWidth);

		var animation = $('#' + el_id).find('.animation-icon');

		$(animation).css("display", "block");
		$(animation).css('left',
				($(document.body).width() / 2) - ($(animation).width() / 2));
		$(animation).css('top',
				($(document.body).height() / 2) - ($(animation).height() / 2));

		// $('#'+el_id).css('opacity','1');
		$('#' + el_id).fadeTo('fast', '1');
		$('#' + el_id).css('visibility', 'visible');

		$(document.body).append(
				'<div class="lockScreen" id="lockScreen"></div>');
		$('#lockScreen').css('width', Width);
		$('#lockScreen').css('height', height);
		$('#lockScreen').css('background', '#ffffff');
		$('#lockScreen').css('top', '0');
		$('#lockScreen').css('left', '0');
		$('#lockScreen').css('position', 'absolute');
		$('#lockScreen').css('opacity', '0');
		$('#lockScreen').css('z-index', '9999998');
		$('#' + el_id).css('z-index', '9999999');
		$('#lockScreen').fadeTo('fast', '0.2');

		// this.increaseTask();
		// if (this.taskCount == 1)
		// this.fadeStage = 0;
		// var el = document.getElementById(el_id);
		// if (el) {
		// // fade out
		// if (this.taskCount == 1) { // init loading
		// //setTimeout('xhrFadeOut("' + el_id + '", '+ delay +')', delay);
		// setTimeout('XhrStatus.fadeOut("' + el_id + '", '+ delay +')', delay);
		// this.isFadingOut = true;
		// }
		// }
	},
	fadeOut : function(el_id, delay) {
		var rad = 1.5707963267948966;
		if (this.fadeStage > 100) {
			this.fadeStage = 0;
			this.isFadingOut = false;
		} else if (this.taskCount > 0) {
			var el = document.getElementById(el_id);
			el.style.opacity = Math.sin((this.fadeStage / 100) * rad);
			el.style.visibility = "visible";
			this.fadeStage += 10;
			// setTimeout('xhrFadeOut("' + el_id + '", '+ delay +')', delay);
			setTimeout('XhrStatus.fadeOut("' + el_id + '", ' + delay + ')',
					delay);
		}
	},
	stop : function(el_id, delay) {
		$('#lockScreen').fadeTo('fast', '0', function() {
			$('.lockScreen').remove();
		});

		var animation = $('#' + el_id).find('.animation-icon');
		$(animation).css("display", "none");

		// $('#'+el_id).css('opacity','0');
		$('#' + el_id).fadeTo('fast', '0', function() {
			$('#' + el_id).css('left', (-10));
			$('#' + el_id).css('top', (-10));
		});
		// var self = this;
		// this.decreaseTask();
		// var el = document.getElementById(el_id);
		// if (el) {
		// if (this.taskCount == 0) {
		// //setTimeout('xhrFadeIn("' + el_id + '", '+ delay +')', delay);
		// setTimeout('XhrStatus.fadeIn("' + el_id + '", '+ delay +')', delay);
		// this.isFadingIn = true;
		// }
		// }
	},
	fadeIn : function(el_id, delay) {
		var self = this;
		var el = document.getElementById(el_id);
		if (this.fadeStage > 100) {
			this.fadeStage = 0;
			this.isFadingIn = false;
		} else if (this.taskCount < 1) {
			el.style.opacity = (100 - this.fadeStage) / 100;
			this.fadeStage += 10;
			// setTimeout('xhrFadeIn("' + el_id + '", '+ delay +')', delay);
			setTimeout('XhrStatus.fadeIn("' + el_id + '", ' + delay + ')',
					delay);
		}
	}
}

/*
 * Back Button Fixxerrrr was
 * http://codinginparadise.org/weblog/2005/08/ajax-tutorial-saving-session-across.html
 * but later changed into anchor based history
 */

var HistoryBase = Base.extend({
	currentState : 0,
	maxState : 0,
	HistoryStack : [],
	isJumping : false,
	log : function(msg) {
		if (console)
			console.log('GtfwJsLog>> ' + msg);
	},
	initialize : function() {
		if (this.observe()) // handl bookmark like URL
			return;

		var initObj = {
			setLocation : function(x) {/* alert('0'); */
				document.location.href = x;
			}
		};
		this.log('initialize: ' + document.location.href.replace(/#.*/, ''));
		this.pushStack(initObj, initObj.setLocation, [ document.location.href
				.replace(/#.*/, '') ], document.location.href
				.replace(/#.*/, ''));

	},
	pushStack : function(obj, func, args, unique_url) {
		this.currentState++;
		this.HistoryStack[this.currentState] = {
			'obj' : obj,
			'func' : func,
			'args' : args,
			unique : unique_url
		};
		this.maxState = this.currentState;
		this.saveState({
			state : this.currentState,
			data : unique_url
		});
	},
	execCurrentStack : function(stack_idx) {
		// this.HistoryStack[this.currentState].func.apply(this.HistoryStack[this.currentState].obj,
		// this.HistoryStack[this.currentState].args);
		if (typeof this.HistoryStack[stack_idx] == 'object')
			this.HistoryStack[this.currentState].func.apply(
					this.HistoryStack[stack_idx].obj,
					this.HistoryStack[stack_idx].args);
		else
			this.log('nothing to exec in HistoryStack[' + stack_idx + ']');
	},
	saveState : function(message) {
		// url hash history
		var url = document.location.href;
		this.log('saveState::currentURL = ' + url);
		// alert('iframe: ' + url)
		var hash = this.createHash(message.state, message.data);
		// if (/#/.test(url))
		// url = url.replace(/#.*/, '#' + hash);
		// else
		url = url.replace(/#.*/, '');
		url += '#' + hash;

		this.log('saveState: ' + url);
		document.location.href = url;
	},
	observe : function() {
		if (this.isJumping) {
			this.log("Is jumping, don't observe yet!");
			return;
		}
		var url = document.location.href;
		if (/#/.test(url)) {
			var hash = /(.*?)#(.*)/.exec(url);
			if (hash != null) {
				var newState = this.extractHash(hash[2]);
				if (newState != false) {
					this.notify(newState[0], newState[1]);
					return true;
				}
			}
		}
		return false;
	},
	notify : function(state, unique_url) {
		// this.log('HistoryStack[state]: ' + this.HistoryStack[state]);
		if ((this.HistoryStack[state] == null)
				|| (this.HistoryStack[state].unique != unique_url)) {
			// Bookmark jump!!!
			// this.log('HistoryStack unique_url: ' +
			// this.HistoryStack[state].unique);
			this.log('should jump to: ' + unique_url);
			// var hash = this.extractHash(unique_url);

			unique_url = unique_url.replace(/ascomponent=\d+/, '');
			this.isJumping = true; // don't observe while jumping;
			document.location.href = unique_url;
			return;
		} else if (state != this.currentState) {
			this.currentState = state;
			this.log('Should exec state: #' + state);
			this.execCurrentStack(state);
		}
	},
	extractHash : function(str) {
		var match = /hash=(.*)&data=(.*)/.exec(str);
		if (match != null) {
			return [ match[1], unescape(match[2]) ];
		} else
			return false;
	},
	createHash : function(state, data) {
		// remove ascomponent from data
		// data = data.replace(/ascomponent=\d+/, '');
		var hash = 'hash=' + state + '&data=' + escape(data);
		this.log('creating hash: ' + hash);
		return hash;
	}
});

// var History = new HistoryBase();

function initFunc() {
	// alert('doc loaded');
	History.initialize();
	window.setInterval('History.observe()', 1500);
}

// FormHelperManager.addLoadEvent(initFunc);
