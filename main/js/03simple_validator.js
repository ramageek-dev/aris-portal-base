/*
Simple Validator
A set of Behaviour libary based rule to provide validation function
Goal: enhance server side validation mechanism

@author Akhmad Fathonih (akhmadf@gmail.com)
@version 0.1
@copyright 2006
@see FormHelper.js
 */

var myrules = {
	'.std_form' : function(element) {
		element.onsubmit = function() {
			return this.canSubmit();
		}, element.canSubmit = function() {
			// alert('submitting form');
			if (this.isRequirementFulfilled())
				return true;
			else {
				// disable submit button
				return false;
			}
		}, element.getControls = function() {
			var res = new Array();
			var controls = this.getElementsByTagName("input"); // TODO: include
																// SELECT
			// alert('Got ' + controls.length + ' input elements');

			for (i = 0; i < controls.length; i++) {
				if ((controls.item(i).type != 'submit')
						&& (controls.item(i).type != 'reset')
						&& (controls.item(i).type != 'radio')
						&& (controls.item(i).type != 'checkbox')
						&& (controls.item(i).type != 'hidden'))
					res.push(controls.item(i));
			}

			var controls = this.getElementsByTagName("select"); // TODO: include
																// SELECT
			// alert('Got ' + selects.length + ' select elements');
			for (i = 0; i < controls.length; i++) {
				// alert('pushing select');
				res.push(controls.item(i));
			}

			var controls = this.getElementsByTagName("textarea"); // TODO:
																	// include
																	// TEXTAREA
			// alert('Got ' + controls.length + ' textarea elements');
			for (i = 0; i < controls.length; i++) {
				// alert('pushing select');
				res.push(controls.item(i));
			}

			// alert('Got ' + res.length + ' elements');
			return res;
		}, element.isRequirementFulfilled = function() {
			// loop over form member and execute is valid
			var controls = this.getControls(); // TODO: include SELECT
			// alert('isRequirementFulfilled::Got ' + controls.length + '
			// elements');
			var res = true;

			for (i = 0; i < controls.length; i++) {
				// controls[i].style.backgroundColor = '#CCCCCC';
				// alert('Checking item(' + i + ') named: ' + controls[i].name);

				// some control may not have the isvalid fnuction for it's not
				// tagged with any simple_validator rules. Be merciful
				if (!controls[i].isValid)
					continue;

				var res = controls[i].isValid();
				if (!res) {
					var msg = 'Kesalahan Pengisian Data ';
					if (controls[i].getErrorMessage())
						msg += ': ' + controls[i].getErrorMessage();

					// alert(controls[i].showBalloon);
					if (controls[i].isBalloonable)
						controls[i].showBalloon(controls[i].getErrorMessage());
					else
						alert(msg);
					controls[i].notifyUser();
					break;
				} else {
					controls[i].resetNotifyUser();
					if (controls[i].isBalloonable)
						controls[i].hideBalloon();
				}

			}

			// debug
			// if (res) alert('Everything is OK');

			return res;
		}
	}
};
Behaviour.register(myrules);
