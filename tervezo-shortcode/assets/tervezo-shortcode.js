function autocomplete(inp, arr) {
  var currentFocus;
  inp.addEventListener("input", function () {
    var a, b, i, val = this.value;
    closeAllLists();
    if (!val) {
      return false;
    }
    currentFocus = -1;
    a = document.createElement("DIV");
    a.setAttribute("id", this.id + "autocomplete-list");
    a.setAttribute("class", "autocomplete-items");
    this.parentNode.appendChild(a);
    for (i = 0; i < arr.length; i++) {
      if (arr[i].substr(0, val.length).toUpperCase() === val.toUpperCase()) {
        b = document.createElement("DIV");
        b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
        b.innerHTML += arr[i].substr(val.length);
        b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
        b.addEventListener("click", function () {
          inp.value = this.getElementsByTagName("input")[0].value;
          closeAllLists();
        });
        a.appendChild(b);
      }
    }
  });
  inp.addEventListener("keydown", function (e) {
    var x = document.getElementById(this.id + "autocomplete-list");
    if (x) {
      x = x.getElementsByTagName("div");
    }
    if (e.keyCode === 40) {
      currentFocus++;
      addActive(x);
    } else if (e.keyCode === 38) {
      currentFocus--;
      addActive(x);
    } else if (e.keyCode === 13) {
      e.preventDefault();
      if (currentFocus > -1) {
        if (x) {
          x[currentFocus].click();
        }
      }
    }
  });

  function addActive(x) {
    if (!x) {
      return false;
    }
    removeActive(x);
    if (currentFocus >= x.length) {
      currentFocus = 0;
    }
    if (currentFocus < 0) {
      currentFocus = (x.length - 1);
    }
    x[currentFocus].classList.add("autocomplete-active");
  }

  function removeActive(x) {
    for (var i = 0; i < x.length; i++) {
      x[i].classList.remove("autocomplete-active");
    }
  }

  function closeAllLists(elmnt) {
    var x = document.getElementsByClassName("autocomplete-items");
    for (var i = 0; i < x.length; i++) {
      if (elmnt !== x[i] && elmnt !== inp) {
        x[i].parentNode.removeChild(x[i]);
      }
    }
  }

  document.addEventListener("click", function (e) {
    closeAllLists(e.target);
  });
}

function sleep(time) {
  return new Promise(function (resolve) {
    setTimeout(resolve, time);
  });
}

function completeyear() {
  try {
    var list = document.getElementById("yearautocomplete-list");
    if (list && list.getElementsByTagName("input")[0]) {
      document.getElementById("year").value = list.getElementsByTagName("input")[0].value;
    }
  } catch (error) {}
  sleep(500).then(function () {
    var x = document.getElementsByClassName("autocomplete-items");
    for (var i = 0; i < x.length; i++) {
      x[i].parentNode.removeChild(x[i]);
    }
  });
}

function check() {
  var yearValue = document.getElementById('year').value;
  var monthValue = document.getElementById('month').value;
  var dayValue = document.getElementById('day').value;
  var nameValue = document.getElementById('nev').value;

  if (yearValue === "") { alert("Nem adtad meg a születési évet!"); return false; }
  if (monthValue === "") { alert("Nem adtad meg a születési hónapot!"); return false; }
  if (dayValue === "") { alert("Nem adtad meg a születési napot!"); return false; }
  if (isNaN(yearValue)) { alert("Az évszámot számmal írd be."); return false; }
  if (isNaN(monthValue)) { alert("A hónap számát számmal írd be."); return false; }
  if (isNaN(dayValue)) { alert("A napot számmal írd be."); return false; }
  if (parseInt(yearValue, 10) < 1902 || parseInt(yearValue, 10) > 2037) { alert("A születési évnek 1902 és 2037 közé kell esnie."); return false; }
  if (parseInt(monthValue, 10) > 12 || parseInt(monthValue, 10) < 1) { alert("A születési hónapnak 1 és 12 közé kell esnie."); return false; }
  if (parseInt(dayValue, 10) > 31 || parseInt(dayValue, 10) < 1) { alert("A születési napnak 1 és 31 közé kell esnie."); return false; }
  if (nameValue === "") { alert("Nem adtad meg a nevet! Becenév is lehet."); return false; }
  return true;
}

document.addEventListener('DOMContentLoaded', function () {
  if (typeof tervezoYearList !== 'undefined') {
    var yearInput = document.getElementById('year');
    if (yearInput) {
      autocomplete(yearInput, tervezoYearList);
    }
  }
});
