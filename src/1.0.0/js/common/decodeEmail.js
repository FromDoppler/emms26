const getURLParam = (searchedParam) => {
  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  return urlParams.get(searchedParam);
};

// Function to decode email
const fromHex = (hex) => {
  var str = "";
  for (var i = 0; i < hex.length; i += 2) {
    var v = parseInt(hex.substr(i, 2), 16);
    if (v) str += String.fromCharCode(v);
  }
  return str;
};

// Function to encode email
const toHex = (str) => {
  var result = "";
  for (var i = 0; i < str.length; i++) {
    result += str.charCodeAt(i).toString(16);
  }
  return result;
};

const getEncodeURLEmail = () => {
  const urlParamHash = getURLParam("dplrid");

  if (urlParamHash) {
    let urlEmailDecode = fromHex(urlParamHash);
    return urlEmailDecode;
  }

  return false;
};

const loadEmail = (urlEmailDecode) => {
  if (urlEmailDecode) {
    document.querySelectorAll("form").forEach((form) => {
      form.querySelector('input[name="email"]').value = urlEmailDecode;
    });
  }
};

export { getEncodeURLEmail, loadEmail, toHex, fromHex };
