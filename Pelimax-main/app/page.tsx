"use client";

import { useState } from "react";
import { AuthProvider, useAuth } from "@/lib/auth-context";
import LoginScreen from "@/components/login-screen";
import RegisterScreen from "@/components/register-screen";
import RecoverScreen from "@/components/recover-screen";
import CatalogScreen from "@/components/catalog-screen";

type Screen = "login" | "register" | "recover";

function AppContent() {
  const { isLoggedIn } = useAuth();
  const [screen, setScreen] = useState<Screen>("login");

  if (isLoggedIn) {
    return <CatalogScreen />;
  }

  switch (screen) {
    case "register":
      return <RegisterScreen onGoLogin={() => setScreen("login")} />;
    case "recover":
      return <RecoverScreen onGoLogin={() => setScreen("login")} />;
    default:
      return (
        <LoginScreen
          onGoRegister={() => setScreen("register")}
          onGoRecover={() => setScreen("recover")}
        />
      );
  }
}

export default function Page() {
  return (
    <AuthProvider>
      <AppContent />
    </AuthProvider>
  );
}
