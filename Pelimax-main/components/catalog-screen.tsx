"use client";

import { useState, useEffect, useMemo } from "react";
import {
  Film,
  LogOut,
  Search,
  Loader2,
  RefreshCcw,
  X,
} from "lucide-react";
import { getPeliculas, type Pelicula } from "@/lib/api";
import { useAuth } from "@/lib/auth-context";
import MovieCard from "./movie-card";
import MovieModal from "./movie-modal";

export default function CatalogScreen() {
  const { user, logout } = useAuth();
  const [peliculas, setPeliculas] = useState<Pelicula[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState("");
  const [search, setSearch] = useState("");
  const [selectedMovie, setSelectedMovie] = useState<Pelicula | null>(null);

  async function fetchMovies() {
    setLoading(true);
    setError("");
    try {
      const res = await getPeliculas();
      if (res.status === "success" && res.peliculas) {
        setPeliculas(res.peliculas);
      } else {
        setError("No se pudieron cargar las peliculas.");
      }
    } catch {
      setError("Error de conexion. Intenta de nuevo.");
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    fetchMovies();
  }, []);

  // Group movies by genre
  const moviesByGenre = useMemo(() => {
    const filtered = search
      ? peliculas.filter(
          (p) =>
            p.nombre.toLowerCase().includes(search.toLowerCase()) ||
            p.genero.toLowerCase().includes(search.toLowerCase())
        )
      : peliculas;

    const groups: Record<string, Pelicula[]> = {};
    filtered.forEach((p) => {
      const genre = p.genero || "Otros";
      if (!groups[genre]) groups[genre] = [];
      groups[genre].push(p);
    });
    return groups;
  }, [peliculas, search]);

  const genreKeys = Object.keys(moviesByGenre);

  return (
    <div className="min-h-dvh bg-cinema-bg">
      {/* Navbar */}
      <nav className="sticky top-0 z-40 border-b-2 border-cinema-gold bg-cinema-bg/95 backdrop-blur-md">
        <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-3">
          {/* Logo */}
          <div className="flex items-center gap-2">
            <div className="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-cinema-gold to-cinema-gold-light shadow shadow-cinema-gold/20">
              <Film className="h-5 w-5 text-cinema-bg" />
            </div>
            <span className="hidden bg-gradient-to-r from-cinema-gold to-cinema-gold-light bg-clip-text text-lg font-bold tracking-tight text-transparent sm:block">
              GOLDEN CINEMA
            </span>
          </div>

          {/* User & Logout */}
          <div className="flex items-center gap-3">
            <div className="flex items-center gap-2 rounded-full border border-cinema-gold/20 bg-cinema-gold/10 px-3 py-1.5">
              <div className="flex h-6 w-6 items-center justify-center rounded-full bg-cinema-gold text-xs font-bold text-cinema-bg">
                {user?.nombre?.charAt(0).toUpperCase() || "U"}
              </div>
              <span className="max-w-[100px] truncate text-xs font-medium text-white">
                {user?.nombre || "Usuario"}
              </span>
            </div>
            <button
              onClick={logout}
              className="flex h-9 w-9 items-center justify-center rounded-full border border-cinema-muted/30 text-cinema-muted transition-colors hover:border-red-400 hover:text-red-400 active:scale-95"
              aria-label="Cerrar sesion"
            >
              <LogOut className="h-4 w-4" />
            </button>
          </div>
        </div>

        {/* Search */}
        <div className="mx-auto max-w-7xl px-4 pb-3">
          <div className="group relative">
            <Search className="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-cinema-muted transition-colors group-focus-within:text-cinema-gold" />
            <input
              type="text"
              placeholder="Buscar peliculas o generos..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="w-full rounded-full border-2 border-cinema-gold/20 bg-cinema-surface py-2.5 pl-10 pr-10 text-sm text-white placeholder-cinema-muted outline-none transition-all focus:border-cinema-gold focus:ring-2 focus:ring-cinema-gold/20"
            />
            {search && (
              <button
                onClick={() => setSearch("")}
                className="absolute right-3 top-1/2 -translate-y-1/2 text-cinema-muted hover:text-white"
                aria-label="Limpiar busqueda"
              >
                <X className="h-4 w-4" />
              </button>
            )}
          </div>
        </div>
      </nav>

      {/* Content */}
      <main className="mx-auto max-w-7xl px-4 py-6">
        {/* Hero / Featured (first movie) */}
        {!loading && peliculas.length > 0 && !search && (
          <div className="mb-8">
            <h2 className="mb-3 flex items-center gap-2 text-lg font-bold text-white">
              <span className="flex h-8 w-8 items-center justify-center rounded-full bg-cinema-gold/10 text-cinema-gold">
                <Film className="h-4 w-4" />
              </span>
              Destacado
            </h2>
            <button
              onClick={() => setSelectedMovie(peliculas[0])}
              className="group relative w-full overflow-hidden rounded-2xl border border-cinema-gold/20 shadow-xl active:scale-[0.99]"
            >
              <div className="relative aspect-video w-full bg-cinema-surface">
                <img
                  src={peliculas[0].imagen}
                  alt={peliculas[0].nombre}
                  className="h-full w-full object-cover brightness-75 transition-transform group-hover:scale-105"
                  crossOrigin="anonymous"
                />
                <div className="absolute inset-0 bg-gradient-to-t from-cinema-bg via-transparent to-transparent" />
                <div className="absolute bottom-0 left-0 right-0 p-5">
                  <span className="mb-2 inline-block rounded-full bg-cinema-gold px-3 py-1 text-xs font-bold text-cinema-bg">
                    {peliculas[0].genero}
                  </span>
                  <h3 className="text-xl font-bold text-cinema-gold drop-shadow-lg md:text-2xl">
                    {peliculas[0].nombre}
                  </h3>
                  <p className="mt-1 line-clamp-2 text-sm text-white/80">
                    {peliculas[0].descripcion}
                  </p>
                </div>
              </div>
            </button>
          </div>
        )}

        {/* Loading */}
        {loading && (
          <div className="flex flex-col items-center justify-center py-20">
            <Loader2 className="h-10 w-10 animate-spin text-cinema-gold" />
            <p className="mt-4 text-sm text-cinema-muted">
              Cargando peliculas...
            </p>
          </div>
        )}

        {/* Error */}
        {error && !loading && (
          <div className="flex flex-col items-center justify-center py-20">
            <p className="mb-4 text-center text-cinema-muted">{error}</p>
            <button
              onClick={fetchMovies}
              className="flex items-center gap-2 rounded-xl bg-cinema-gold px-6 py-2.5 font-semibold text-cinema-bg transition-all active:scale-95"
            >
              <RefreshCcw className="h-4 w-4" />
              Reintentar
            </button>
          </div>
        )}

        {/* Movies by Genre */}
        {!loading && !error && genreKeys.length > 0 && (
          <div className="flex flex-col gap-8">
            {genreKeys.map((genre) => (
              <section key={genre}>
                <h2 className="mb-3 flex items-center gap-2 border-l-4 border-cinema-gold pl-3 text-base font-bold text-white md:text-lg">
                  {genre}
                </h2>
                <div className="flex gap-3 overflow-x-auto pb-3" style={{ scrollbarWidth: "thin", scrollbarColor: "#d4af37 transparent" }}>
                  {moviesByGenre[genre].map((movie) => (
                    <MovieCard
                      key={movie.id}
                      movie={movie}
                      onClick={() => setSelectedMovie(movie)}
                    />
                  ))}
                </div>
              </section>
            ))}
          </div>
        )}

        {/* No results */}
        {!loading && !error && genreKeys.length === 0 && (
          <div className="flex flex-col items-center justify-center py-20">
            <Search className="h-12 w-12 text-cinema-muted/30" />
            <p className="mt-4 text-center text-cinema-muted">
              {search
                ? `No se encontraron resultados para "${search}"`
                : "No hay peliculas disponibles."}
            </p>
          </div>
        )}
      </main>

      {/* Movie Modal */}
      <MovieModal
        movie={selectedMovie}
        onClose={() => setSelectedMovie(null)}
      />
    </div>
  );
}
