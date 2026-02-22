"use client";

import { useState, useEffect, useCallback } from "react";
import { X } from "lucide-react";
import type { Pelicula } from "@/lib/api";

interface MovieModalProps {
  movie: Pelicula | null;
  onClose: () => void;
}

function convertToEmbed(url: string): string {
  if (!url) return "";
  if (url.includes("watch?v=")) {
    const parts = url.split("watch?v=");
    return "https://www.youtube.com/embed/" + parts[1]?.substring(0, 11);
  } else if (url.includes("youtu.be/")) {
    const parts = url.split("youtu.be/");
    return "https://www.youtube.com/embed/" + parts[1]?.substring(0, 11);
  }
  return url;
}

export default function MovieModal({ movie, onClose }: MovieModalProps) {
  const [imgError, setImgError] = useState(false);

  const handleEsc = useCallback(
    (e: KeyboardEvent) => {
      if (e.key === "Escape") onClose();
    },
    [onClose]
  );

  useEffect(() => {
    if (movie) {
      document.body.style.overflow = "hidden";
      document.addEventListener("keydown", handleEsc);
    }
    return () => {
      document.body.style.overflow = "";
      document.removeEventListener("keydown", handleEsc);
    };
  }, [movie, handleEsc]);

  if (!movie) return null;

  const embedUrl = convertToEmbed(movie.video_url || "");
  const imageSrc = movie.imagen || "";

  return (
    <div
      className="fixed inset-0 z-50 flex items-end justify-center bg-black/70 backdrop-blur-sm md:items-center"
      onClick={onClose}
      role="dialog"
      aria-modal="true"
      aria-label={`Detalles de ${movie.nombre}`}
    >
      <div
        className="max-h-[90dvh] w-full overflow-y-auto rounded-t-2xl border-t border-cinema-gold/30 bg-cinema-card md:max-w-lg md:rounded-2xl md:border"
        onClick={(e) => e.stopPropagation()}
      >
        {/* Header */}
        <div className="sticky top-0 z-10 flex items-center justify-between border-b border-cinema-gold/20 bg-cinema-card/95 px-5 py-3.5 backdrop-blur-sm">
          <h3 className="truncate pr-4 text-lg font-bold text-cinema-gold">
            {movie.nombre}
          </h3>
          <button
            onClick={onClose}
            className="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-white/10 text-white transition-colors hover:bg-white/20"
            aria-label="Cerrar"
          >
            <X className="h-4 w-4" />
          </button>
        </div>

        {/* Content */}
        <div className="p-5">
          {/* Video or Image */}
          {embedUrl ? (
            <div className="mb-4 overflow-hidden rounded-xl border border-cinema-gold/20">
              <div className="relative w-full" style={{ paddingBottom: "56.25%" }}>
                <iframe
                  src={embedUrl}
                  title={movie.nombre}
                  className="absolute inset-0 h-full w-full"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowFullScreen
                />
              </div>
            </div>
          ) : !imgError && imageSrc ? (
            <div className="mb-4 overflow-hidden rounded-xl border border-cinema-gold/20">
              <img
                src={imageSrc}
                alt={movie.nombre}
                className="w-full object-cover"
                style={{ maxHeight: "300px" }}
                onError={() => setImgError(true)}
                crossOrigin="anonymous"
              />
            </div>
          ) : null}

          {/* Details */}
          <div className="rounded-xl bg-black/20 p-4">
            <div className="mb-3 flex items-center gap-2">
              <span className="rounded-full bg-cinema-gold px-3 py-1 text-xs font-bold text-cinema-bg">
                {movie.genero}
              </span>
            </div>

            <div className="mb-2">
              <span className="text-xs font-semibold uppercase tracking-wider text-cinema-muted">
                Descripcion
              </span>
            </div>
            <p className="text-sm leading-relaxed text-white/80">
              {movie.descripcion || "Sin descripcion disponible."}
            </p>
          </div>
        </div>
      </div>
    </div>
  );
}
