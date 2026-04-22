"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useState, useEffect } from "react";
import {
  Menu,
  X,
  Home,
  Info,
  BookOpen,
  Calendar,
  Mail,
  ChevronRight
} from "lucide-react";

const navLinks = [
  { name: "Home", href: "/", icon: Home },
  { name: "About", href: "/about", icon: Info },
  { name: "Sermons", href: "/sermons", icon: BookOpen },
  { name: "Events", href: "/events", icon: Calendar },
  { name: "Contact", href: "/contact", icon: Mail },
  { name: "Pastors", href: "/pastors", icon: Mail },
  { name: "Ministries", href: "/ministries", icon: Mail },
  
];

export default function Navbar() {
  const pathname = usePathname();
  const [isOpen, setIsOpen] = useState(false);

  // Prevent scrolling when drawer is open
  useEffect(() => {
    if (isOpen) {
      document.body.style.overflow = "hidden";
    } else {
      document.body.style.overflow = "unset";
    }
  }, [isOpen]);

  return (
    <>
      <nav className="w-full border-b border-border-main bg-background/80 backdrop-blur-md sticky top-0 z-40">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex justify-between h-20 items-center">
            <div className="flex-shrink-0">
              <Link href="/" className="flex items-center space-x-2">
                {/* <span className="text-2xl font-black tracking-tighter text-blue-600">AIC</span> */}
                <span className="text-2xl font-light tracking-widest text-foreground border-l border-border-main pl-2">AIC BONDENI</span>
              </Link>
            </div>

            {/* Desktop Navigation */}
            <div className="hidden md:flex md:space-x-8">
              {navLinks.map((link) => (
                <Link
                  key={link.name}
                  href={link.href}
                  className={`inline-flex items-center px-1 pt-1 text-sm font-semibold transition-all duration-200 ${pathname === link.href
                    ? "text-primary border-b-2 border-primary"
                    : "text-muted hover:text-foreground hover:border-border-main"
                    }`}
                >
                  {link.name}
                </Link>
              ))}
            </div>

            {/* Mobile menu button */}
            <div className="md:hidden flex items-center">
              <button
                onClick={() => setIsOpen(true)}
                className="inline-flex items-center justify-center p-2.5 rounded-xl text-muted hover:text-primary hover:bg-primary-muted transition-all active:scale-95"
              >
                <Menu size={24} />
              </button>
            </div>
          </div>
        </div>
      </nav>

      {/* Side Drawer Overlay */}
      <div
        className={`fixed inset-0 bg-black/40 backdrop-blur-sm z-50 transition-opacity duration-300 ${isOpen ? "opacity-100 pointer-events-auto" : "opacity-0 pointer-events-none"}`}
        onClick={() => setIsOpen(false)}
      />

      {/* Side Drawer Content */}
      <div
        className={`fixed top-0 right-0 h-full w-[80%] max-w-sm bg-white z-50 shadow-2xl transition-transform duration-500 cubic-bezier(0.4, 0, 0.2, 1) ${isOpen ? "translate-x-0" : "translate-x-full"}`}
      >
        <div className="flex flex-col h-full">
          {/* Drawer Header */}
          <div className="flex items-center justify-between p-6 border-b border-border-main">
            <div className="flex items-center space-x-2">
              {/* <span className="text-xl font-black tracking-tighter text-blue-600">AIC</span> */}
              <span className="text-xl font-light tracking-widest text-foreground">AIC BONDENI</span>
            </div>
            <button
              onClick={() => setIsOpen(false)}
              className="p-2 rounded-lg text-muted hover:text-foreground hover:bg-border-main transition-colors"
            >
              <X size={20} />
            </button>
          </div>

          {/* Drawer Links */}
          <div className="flex-1 overflow-y-auto pt-4">
            <nav className="px-4 space-y-2">
              {navLinks.map((link) => {
                const Icon = link.icon;
                const isActive = pathname === link.href;

                return (
                  <Link
                    key={link.name}
                    href={link.href}
                    onClick={() => setIsOpen(false)}
                    className={`flex items-center justify-between w-full p-4 rounded-2xl transition-all ${isActive
                      ? "bg-primary-muted text-primary"
                      : "text-muted-foreground hover:bg-primary-muted hover:text-primary"
                      }`}
                  >
                    <div className="flex items-center space-x-4">
                      <div className={`p-2 rounded-xl ${isActive ? "bg-background shadow-sm" : "bg-border-main text-muted"}`}>
                        <Icon size={20} />
                      </div>
                      <span className="font-semibold">{link.name}</span>
                    </div>
                    {isActive && <ChevronRight size={18} className="text-primary/60" />}
                  </Link>
                );
              })}
            </nav>
          </div>

          {/* Drawer Footer */}
          <div className="p-8 border-t border-border-main bg-border-main/20">
            <p className="text-xs text-muted text-center">
              Welcome to our fellowship.<br />
              © {new Date().getFullYear()} AIC Bondeni
            </p>
          </div>
        </div>
      </div>
    </>
  );
}
