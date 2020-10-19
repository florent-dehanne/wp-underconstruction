jQuery(function($) {
  toggleUnderConstructionForm();
  toggleUnderConstructionFields();

  $('#wp_underconstruction').click(function() {
    toggleUnderConstructionForm();
  });

  $('input[name="wp_underconstruction[mode]"]').click(function() {
    toggleUnderConstructionFields();
  });
});

function toggleUnderConstructionForm()
{
  if (jQuery('#wp_underconstruction').is(':checked'))
  {
    $('.wp_underconstruction-form').show();
  }
  else {
    $('.wp_underconstruction-form').hide();
  }
}

function toggleUnderConstructionFields()
{
  $('.display-methods-fields tr').hide();

  var selected = $('input[name="wp_underconstruction[mode]"]:checked').val();

  $('.display-methods-fields tr.field-' + selected).show();
}