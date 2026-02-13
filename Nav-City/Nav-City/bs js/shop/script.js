
let mybutton = document.getElementById("topButton");


window.onscroll = function() {scrollFunction()};

function scrollFunction() {

  if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
    mybutton.style.opacity = "1";
    mybutton.style.cursor = "pointer";
  } else {
    mybutton.style.opacity = "0";
    mybutton.style.cursor = "auto";
  }
}

function topFunction() {
  document.body.scrollTop = 0;
  document.documentElement.scrollTop = 0;
}