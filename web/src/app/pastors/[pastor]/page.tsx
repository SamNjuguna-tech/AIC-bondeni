import { METHODS } from "http";
import { useParams } from "next/navigation"
import Image from "next/image";
import { useEffect } from "react";

export const metadata = {
  title: "pastor | AIC Bondeni",
  description: "Stay updated with upcoming pastor at AIC Bondeni.",
};

export default function pastor() {
  
  function fetchContent(){
  fetch("/prayer_request",{
    method:"POST",
  })
  }
  
 useEffect
  return (
    <div className="flex-grow flex items-center justify-center">
      <h1 className="text-4xl md:text-6xl font-extrabold tracking-tight text-foreground animate-in fade-in zoom-in duration-700">
        {pastor.name}

      </h1>
    </div>
  );
}
