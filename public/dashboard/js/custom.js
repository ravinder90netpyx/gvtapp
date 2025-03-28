$(function(){
  'use strict';
  window.addEventListener('load', function(){
    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.getElementsByClassName('needs-validation');
    // Loop over them and prevent submission
    var validation = Array.prototype.filter.call(forms, function(form){
      form.addEventListener('submit', function(event){
        if(form.checkValidity()===false){
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    });
  }, false);

  // jQuery code here
  if($('.select-all').length>0){
    $('.select-all').click(function(event){
      var this_check = this.checked;
      $('.main-listing-table tbody :checkbox').each(function(){ this.checked = this_check; });
    });
  }
  // $("#div-sidenav").click(function(){ alert(); });
  // $(document).on('click', '#div-sidenav', function(e){
  //     alert(1);
  //     e.preventDefault();
  //     e.stopPropagation();
  //     $('#sidenav-main').toggleClass('mobile-width');
  //     if($('#sidenav-main'),hasClass('mobile-width')){
  //       $('body').removeClass('g-sidenav-show');
  //       $('#sidenav-main').removeClass('mobile-width');
  //     } else{
  //       $('#sidenav-main').addClass('mobile-width');
  //     }
  //   });
});
