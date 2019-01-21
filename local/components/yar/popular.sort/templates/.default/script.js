$(function(){
   $('#sort-popularity').change(function(){
       if($(this).prop("checked")){
           location.href = $(this).data('checked');
       }else{
           location.href = $(this).data('unchecked');
       }
    });
});