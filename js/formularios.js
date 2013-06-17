//ocultar mensaje de error al escribir
$('input[type="text"], input[type="number"], input[type="password"]').keyup(function(){
  $(this).siblings('span.label').hide('fast');
  if ($(this).val()==''){
    $(this).parentsUntil('.control-group').first().parent().removeClass('error');
  }
});
//ocultar mensaje de error al escribir
$('textarea').keyup(function(){
  $(this).siblings('span.label').hide('fast');
  if ($(this).text()==''){
    $(this).parentsUntil('.control-group').first().parent().removeClass('error');
  }
});