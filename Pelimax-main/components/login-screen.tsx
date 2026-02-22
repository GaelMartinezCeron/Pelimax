"use client";

import { useState, type FormEvent } from "react";
import { Film, Mail, Lock, Eye, EyeOff, Loader2 } from "lucide-react";
import { login } from "@/lib/api";
import { useAuth } from "@/lib/auth-context";

interface LoginScreenProps {
  onGoRegister: () => void;
  onGoRecover: () => void;
}

export default function LoginScreen({
  onGoRegister,
  onGoRecover,
}: LoginScreenProps) {
  const { setUser } = useAuth();
  const [correo, setCorreo] = useState("");
  const [password, setPassword] = useState("");
  const [showPass, setShowPass] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState("");

  async function handleSubmit(e: FormEvent) {
    e.preventDefault();
    setError("");
    setLoading(true);

    try {
      const res = await login(correo, password);
      if (res.status === "success" && res.usuario) {
        setUser(res.usuario);
      } else {
        setError(res.mensaje || "Correo o contraseña incorrectos.");
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
        {/* Logo */}
        <div className="mb-10 flex flex-col items-center gap-3">
          <div className="flex h-16 w-16 items-center justify-center rounded-2xl bg-gradient-to-br from-cinema-gold to-cinema-gold-light shadow-lg shadow-cinema-gold/20">
            <Film className="h-8 w-8 text-cinema-bg" />
          </div>
          <h1 className="bg-gradient-to-r from-cinema-gold to-cinema-gold-light bg-clip-text text-2xl font-bold tracking-tight text-transparent">
            GOLDEN CINEMA
          </h1>
        </div>

        {/* Card */}
        <div className="rounded-2xl border border-cinema-gold/10 bg-cinema-card p-6 shadow-2xl">
          <div className="mb-6 text-center">
            <h2 className="text-xl font-bold text-white">Bienvenido</h2>
            <p className="mt-1 text-sm text-cinema-muted">
              Ingresa tus datos para continuar
            </p>
          </div>

          {error && (
            <div className="mb-4 rounded-xl border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400">
              {error}
            </div>
          )}

          <form onSubmit={handleSubmit} className="flex flex-col gap-4">
            {/* Email */}
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

            {/* Password */}
            <div className="group relative">
              <div className="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2">
                <Lock className="h-4 w-4 text-cinema-muted transition-colors group-focus-within:text-cinema-gold" />
              </div>
              <input
                type={showPass ? "text" : "password"}
                placeholder="Contraseña"
                value={password}
                onChange={(e) => setPassword(e.target.value)}
                required
                className="w-full rounded-xl border-2 border-cinema-gold/10 bg-cinema-surface py-3.5 pl-11 pr-12 text-white placeholder-cinema-muted outline-none transition-all focus:border-cinema-gold focus:ring-2 focus:ring-cinema-gold/20"
              />
              <button
                type="button"
                onClick={() => setShowPass(!showPass)}
                className="absolute right-4 top-1/2 -translate-y-1/2 text-cinema-muted transition-colors hover:text-cinema-gold"
              >
                {showPass ? (
                  <EyeOff className="h-4 w-4" />
                ) : (
                  <Eye className="h-4 w-4" />
                )}
              </button>
            </div>

            {/* Forgot password */}
            <div className="flex justify-end">
              <button
                type="button"
                onClick={onGoRecover}
                className="text-sm font-medium text-cinema-gold transition-colors hover:text-cinema-gold-light"
              >
                Olvide mi contraseña
              </button>
            </div>

            {/* Submit */}
            <button
              type="submit"
              disabled={loading}
              className="flex items-center justify-center gap-2 rounded-xl bg-gradient-to-r from-cinema-gold to-cinema-gold-light py-3.5 font-bold text-cinema-bg shadow-lg shadow-cinema-gold/20 transition-all hover:shadow-cinema-gold/40 active:scale-[0.98] disabled:opacity-60"
            >
              {loading ? (
                <Loader2 className="h-5 w-5 animate-spin" />
              ) : (
                "Iniciar sesion"
              )}
            </button>
          </form>

          {/* Divider */}
          <div className="my-6 flex items-center gap-3">
            <div className="h-px flex-1 bg-cinema-gold/10" />
            <span className="text-xs text-cinema-muted">Nuevo por aqui?</span>
            <div className="h-px flex-1 bg-cinema-gold/10" />
          </div>

          {/* Register link */}
          <button
            onClick={onGoRegister}
            className="w-full rounded-xl border-2 border-cinema-gold/30 py-3 font-semibold text-cinema-gold transition-all hover:border-cinema-gold hover:bg-cinema-gold/5 active:scale-[0.98]"
          >
            Crear cuenta
          </button>
        </div>
      </div>
    </div>
  );
}
