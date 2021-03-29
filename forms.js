/**
  * Return a form inputs in a key-value pair. The divID is the id of the div containing the form
  * and the selectors is the types of inputs to take, e.g. 'input,textarea' will search <input> tags and <textarea>
  */
function serializeForm(divID, selectors) {
  var div = document.getElementById(divID);

  if (div != null) {
    var inputs = div.querySelectorAll(selectors);

    if (inputs.length > 0) {
      var data = {};
      for (var i = 0; i < inputs.length; i++) {
        var input = inputs[i];

        if (input.nodeName.toLowerCase() == 'select' && input.multiple) {
          var options = {};
          for (var i = 0, l = input.options.length; i < l; i++) {
            var o = input.options[i];

            if (o.selected) {
              options[i] = o.value;
            }
          }

          data[input.id] = options;
        } else {
          data[input.id] = input.value;
        }
      }

      return data;
    }
  }

  return null;
}

/**
  * Validated the form, returning true if valid, false if not
  */
function validateForm(formId) {
  var form = document.getElementById(formId);

  if (form != null) {
    var valid = form.checkValidity();
    form.classList.add('was-validated');
    return valid;
  }

  return false;
}

/**
  * Remove the value from the select identified by select id if it is found
  */
function removeFromSelectByValue(selectId, value) {
  var select = document.getElementById(selectId);

  if (select != null) {
    for (var i = 0; i < select.length; i++) {
      if (select.options[i].value == value) {
        select.remove(i);
        return;
      }
    }
  }
}

/**
  * Adds a new option to the select identified by the id.
  * If value is null, text is taken to be the value
  */
function addToSelect(selectId, value, text) {
  var select = document.getElementById(selectId);

  if (select != null) {
    var option = document.createElement("option");
    option.text = text;
    if (value != null) {
      option.value = value;
    }

    select.add(option);
  }
}
