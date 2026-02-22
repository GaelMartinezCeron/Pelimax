"use client";

import { useState } from "react";
import type { Pelicula } from "@/lib/api";

interface MovieCardProps {
  movie: Pelicula;
  onClick: () => void;
}

export default function MovieCard({ movie, onClick }: MovieCardProps) {
  const [imgError, setImgError] = useState(false);

  const imageSrc = movie.imagen || "";
  const isBase64 = imageSrc.startsWith("data:image");

  return (
    <button
      onClick={onClick}
      className="group flex w-[140px] flex-shrink-0 flex-col overflow-hidden rounded-xl border border-cinema-gold/10 bg-cinema-card transition-all active:scale-95 md:w-[180px] md:hover:scale-[1.03] md:hover:border-cinema-gold md:hover:shadow-lg md:hover:shadow-cinema-gold/10"
    >
      <div className="relative aspect-[2/3] w-full overflow-hidden bg-cinema-surface">
        {!imgError && imageSrc ? (
          <img
            src={isBase64 ? imageSrc : imageSrc}
            alt={movie.nombre}
            className="h-full w-full object-cover transition-transform group-hover:scale-105"
            onError={() => setImgError(true)}
            loading="lazy"
            crossOrigin="anonymous"
          />
        ) : (
          <div className="flex h-full w-full items-center justify-center text-cinema-muted">
            <svg
              className="h-10 w-10 opacity-30"
              fill="none"
              viewBox="0 0 24 24"
              stroke="currentColor"
            >
              <path
                strokeLinecap="round"
                strokeLinejoin="round"
                strokeWidth={1.5}
                d="M15.75 10.5l4.72-4.72a.75.75 0 011.28.53v11.38a.75.75 0 01-1.28.53l-4.72-4.72M4.5 18.75h9a2.25 2.25 0 002.25-2.25v-9a2.25 2.25 0 00-2.25-2.25h-9A2.25 2.25 0 002.25 7.5v9a2.25 2.25 0 002.25 2.25z"
              />
            </svg>
          </div>
        )}
        {/* Genre badge */}
        <div className="absolute bottom-2 left-2 rounded-full bg-cinema-gold/90 px-2 py-0.5 text-[10px] font-bold text-cinema-bg">
          {movie.genero}
        </div>
      </div>
      <div className="p-2.5">
        <p className="truncate text-left text-xs font-semibold text-white md:text-sm">
          {movie.nombre}
        </p>
      </div>
    </button>
  );
}
