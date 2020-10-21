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
    jQuery('.wp_underconstruction-form').show();
  }
  else {
    jQuery('.wp_underconstruction-form').hide();
  }
}

function toggleUnderConstructionFields()
{
  var selected = jQuery('input[name="wp_underconstruction[mode]"]:checked').val();

  jQuery('.display-methods-fields tr').hide();
  jQuery('.display-methods-fields tr.field-' + selected).show();
}