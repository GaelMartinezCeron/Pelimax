"use client";

import { useState, type FormEvent } from "react";
import {
  Film,
  Mail,
  User,
  ArrowLeft,
  Loader2,
  CheckCircle2,
} from "lucide-react";
import { registro } from "@/lib/api";

interface RegisterScreenProps {
  onGoLogin: () => void;
}

export default function RegisterScreen({ onGoLogin }: RegisterScreenProps) {
  const [nombre, setNombre] = useState("");
  const [paterno, setPaterno] = useState("");
  const [materno, setMaterno] = useState("");
  const [correo, setCorreo] = useState("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");
  const [success, setSuccess] = useState("");

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setError("");
    setSuccess("");
    setLoading(true);

    try {
      const res = await registro(nombre, paterno, materno, correo);
      if (res.status === "success") {
        setSuccess(res.mensaje);
        setNombre("");
        setPaterno("");
        setMaterno("");
        setCorreo("");
      } else {
        setError(res.mensaje || "Error al registrar.");
      }
    } catch {
      setError("Error de conexion. Verifica tu internet e intenta de nuevo.");
    } finally {
      setLoading(false);
    }
  }

  return (
    <div className="flex min-h-dvh flex-col items-center justify-center px-5 py-10">
      {/* Background decoration */}
      <div className="pointer-events-none fixed inset-0 overflow-hidden">
        <div className="absolute -left-32 -top-32 h-64 w-64 rounded-full bg-cinema-gold/5 blur-3xl" />
        <div className="absolute -bottom-32 -right-32 h-64 w-64 rounded-full bg-cinema-gold/5 blur-3xl" />
      </div>

      <div className="relative z-10 w-full max-w-sm">
        {/* Back button */}
        <button
          onClick={onGoLogin}
          className="mb-6 flex items-center gap-2 text-sm font-medium text-cinema-muted transition-colors hover:text-cinema-gold"
        >
          <ArrowLeft className="h-4 w-4" />
          Volver al inicio de sesion
        </button>

        {/* Logo */}
        <div className="mb-8 flex flex-col items-center gap-3">
          <div className="flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br from-cinema-gold to-cinema-gold-light shadow-lg shadow-cinema-gold/20">
            <Film className="h-7 w-7 text-cinema-bg" />
          </div>
          <h1 className="bg-gradient-to-r from-cinema-gold to-cinema-gold-light bg-clip-text text-xl font-bold tracking-tight text-transparent">
            GOLDEN CINEMA
          </h1>
        </div>

        {/* Card */}
        <div className="rounded-2xl border border-cinema-gold/10 bg-cinema-card p-6 shadow-2xl">
          <div className="mb-6 text-center">
            <h2 className="text-xl font-bold text-white">Crear cuenta</h2>
            <p className="mt-1 text-sm text-cinema-muted">
              Se generara una contraseña segura y se enviara a tu correo
            </p>
          </div>

          {error && (
            <div className="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400">
              {error}
            </div>
          )}

          {success && (
            <div className="mb-4 flex flex-col items-center gap-3 rounded-xl border border-green-500/30 bg-green-500/10 px-4 py-5 text-center">
              <CheckCircle2 className="h-10 w-10 text-green-400" />
              <p className="text-sm font-medium text-green-400">{success}</p>
              <button
                onClick={onGoLogin}
                className="mt-2 rounded-lg bg-cinema-gold px-6 py-2 text-sm font-bold text-cinema-bg transition-all hover:bg-cinema-gold-light active:scale-95"
              >
                Ir a iniciar sesion
              </button>
            </div>
          )}

          {!success && (
            <form onSubmit={handleSubmit} className="flex flex-col gap-4">
              {/* Nombre */}
              <div className="group relative">
                <div className="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2">
                  <User className="h-4 w-4 text-cinema-muted transition-colors group-focus-within:text-cinema-gold" />
                </div>
                <input
                  type="text"
                  placeholder="Nombre"
                  value={nombre}
                  onChange={(e) => setNombre(e.target.value)}
                  required
                  className="w-full rounded-xl border-2 border-cinema-gold/10 bg-cinema-surface py-3.5 pl-11 pr-4 text-white placeholder-cinema-muted outline-none transition-all focus:border-cinema-gold focus:ring-2 focus:ring-cinema-gold/20"
                />
              </div>

              {/* Apellido Paterno */}
              <div className="group relative">
                <div className="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2">
                  <User className="h-4 w-4 text-cinema-muted transition-colors group-focus-within:text-cinema-gold" />
                </div>
                <input
                  type="text"
                  placeholder="Apellido paterno"
                  value={paterno}
                  onChange={(e) => setPaterno(e.target.value)}
                  required
                  className="w-full rounded-xl border-2 border-cinema-gold/10 bg-cinema-surface py-3.5 pl-11 pr-4 text-white placeholder-cinema-muted outline-none transition-all focus:border-cinema-gold focus:ring-2 focus:ring-cinema-gold/20"
                />
              </div>

              {/* Apellido Materno */}
              <div className="group relative">
                <div className="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2">
                  <User className="h-4 w-4 text-cinema-muted transition-colors group-focus-within:text-cinema-gold" />
                </div>
                <input
                  type="text"
                  placeholder="Apellido materno"
                  value={materno}
                  onChange={(e) => setMaterno(e.target.value)}
                  required
                  className="w-full rounded-xl border-2 border-cinema-gold/10 bg-cinema-surface py-3.5 pl-11 pr-4 text-white placeholder-cinema-muted outline-none transition-all focus:border-cinema-gold focus:ring-2 focus:ring-cinema-gold/20"
                />
              </div>

              {/* Correo */}
              <div className="group relative">
                <div className="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2">
                  <Mail className="h-4 w-4 text-cinema-muted transition-colors group-focus-within:text-cinema-gold" />
                </div>
                <input
                  type="email"
                  placeholder="Correo electronico"
                  value={correo}
                  onChange={(e) => setCorreo(e.target.value)}
                  required
                  className="w-full rounded-xl border-2 border-cinema-gold/10 bg-cinema-surface py-3.5 pl-11 pr-4 text-white placeholder-cinema-muted outline-none transition-all focus:border-cinema-gold focus:ring-2 focus:ring-cinema-gold/20"
                />
              </div>

              {/* Info box */}
              <div className="rounded-xl border border-cinema-gold/10 bg-cinema-gold/5 px-4 py-3">
                <p className="text-xs leading-relaxed text-cinema-muted">
                  Al registrarte, se generara una contraseña segura
                  automaticamente y sera enviada a tu correo electronico.
                </p>
              </div>

              {/* Submit */}
              <button
                type="submit"
                disabled={loading}
                className="flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cinema-gold to-cinema-gold-light py-3.5 font-bold text-cinema-bg shadow-lg shadow-cinema-gold/20 transition-all hover:shadow-cinema-gold/40 active:scale-[0.98] disabled:opacity-60"
              >
                {loading ? (
                  <>
                    <Loader2 className="h-5 w-5 animate-spin" />
                    Registrando...
                  </>
                ) : (
                  "Crear mi cuenta"
                )}
              </button>
            </form>
          )}
        </div>
      </div>
    </div>
  );
}
