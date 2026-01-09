const dialog = document.getElementById("dialog");
console.log(dialog);
function MAX_kinyit(){
  if (dialog){
    dialog.showModal();
  }
}

function MAX_bezar(){
  if (dialog){
    dialog.close();
  }
}