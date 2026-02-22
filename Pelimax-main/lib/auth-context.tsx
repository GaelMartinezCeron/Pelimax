"use client";

import {
  createContext,
  useContext,
  useState,
  useCallback,
  type ReactNode,
} from "react";
import type { Usuario } from "./api";

interface AuthContextType {
  user: Usuario | null;
  setUser: (user: Usuario | null) => void;
  logout: () => void;
  isLoggedIn: boolean;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [user, setUserState] = useState<Usuario | null>(() => {
    if (typeof window !== "undefined") {
      const stored = sessionStorage.getItem("gc_user");
      return stored ? JSON.parse(stored) : null;
    }
    return null;
  });

  const setUser = useCallback((u: Usuario | null) => {
    setUserState(u);
    if (typeof window !== "undefined") {
      if (u) {
        sessionStorage.setItem("gc_user", JSON.stringify(u));
      } else {
        sessionStorage.removeItem("gc_user");
      }
    }
  }, []);

  const logout = useCallback(() => {
    setUser(null);
  }, [setUser]);

  return (
    <AuthContext.Provider
      value={{ user, setUser, logout, isLoggedIn: !!user }}
    >
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const ctx = useContext(AuthContext);
  if (!ctx) throw new Error("useAuth must be used within AuthProvider");
  return ctx;
}
