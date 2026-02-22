const API_URL =
  "https://sandybrown-manatee-779276.hostingersite.com/api_moviemubi.php";

export interface Usuario {
  id: string;
  nombre: string;
  paterno: string;
  materno: string;
  correo: string;
}

export interface Pelicula {
  id: string;
  nombre: string;
  genero: string;
  descripcion: string;
  imagen: string;
  video_url: string;
}

export interface ApiResponse {
  status: "success" | "error";
  mensaje: string;
  usuario?: Usuario;
  peliculas?: Pelicula[];
}

async function callApi(data: Record<string, string>): Promise<ApiResponse> {
  const res = await fetch(API_URL, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  });

  if (!res.ok) {
    throw new Error("Error de conexion con el servidor");
  }

  return res.json();
}

export async function login(
  correo: string,
  password: string
): Promise<ApiResponse> {
  return callApi({ accion: "login", correo, password });
}

export async function registro(
  nombre: string,
  paterno: string,
  materno: string,
  correo: string
): Promise<ApiResponse> {
  return callApi({ accion: "registro", nombre, paterno, materno, correo });
}

export async function recuperar(correo: string): Promise<ApiResponse> {
  return callApi({ accion: "recuperar", correo });
}

export async function getPeliculas(): Promise<ApiResponse> {
  return callApi({ accion: "get_peliculas" });
}
