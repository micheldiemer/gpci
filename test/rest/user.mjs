const BASE_URL = "https://dev.mshome.net/gpci/backend";
const fetchRoute = async (route, method, params) => {
  const url = `${BASE_URL}${route}`;
  // eslint-disable-next-line no-undef
  document.getElementById("req").innerHTML = prettyPrintJson.toHtml({
    url: url,
    methd: method,
    params: params,
  });

  let code = -1;
  let data = "__INIT__";

  try {
    let res = await fetch(url, {
      method: method,
      headers: {
        "Content-Type": "application/json",
      },
      credentials: "include",
      body: JSON.stringify(params),
    });
    let code = res.status;
    console.log(res);
    const contentType = res.headers.get("content-type");
    const reshd = res.headers;

    if (
      code === 200 ||
      (contentType && contentType.indexOf("application/json") !== -1)
    ) {
      try {
        data = await res.json();
      } catch (err) {
        data = {
          fmt: "text",
          text: await res.text(),
        };
        data = await res.text();
      }
    }

    // eslint-disable-next-line no-undef
    document.getElementById("res").innerHTML = prettyPrintJson.toHtml({
      // reshd: Object.fromEntries(res.headers),
      code: code,
      data: data,
    });
  } catch (err) {
    // eslint-disable-next-line no-undef
    document.getElementById("res").innerHTML = prettyPrintJson.toHtml({
      err: err,
    });
  }

  return { code: code, data: data };
};

const user = {
  name: "",
  password: "",
  id: "",
  token: "",
};

document.getElementById("btnLogin").addEventListener("click", async (event) => {
  event.preventDefault();

  user.name = document.getElementById("name").value;
  user.password = document.getElementById("password").value;

  const userf = {
    name: user.name,
    password: user.password,
  };

  let data = await fetchRoute("/login", "POST", userf);
});
