import { METHODS } from "http";
import { useParams } from "next/navigation";

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
 
  return (
    <div className="flex-grow flex items-center justify-center">
      <h1 className="text-4xl md:text-6xl font-extrabold tracking-tight text-foreground animate-in fade-in zoom-in duration-700">
        
      </h1>
    </div>
  );
}
