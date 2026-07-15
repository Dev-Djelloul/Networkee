import React, { useState } from "react";
import {
  Home,
  Search,
  PlusSquare,
  Heart,
  User,
  MessageCircle,
  Share2,
  MoreHorizontal,
  Send,
  Image as ImageIcon
} from "lucide-react";

export function Mobile() {
  const [likedPosts, setLikedPosts] = useState<Record<number, boolean>>({
    1: false,
    2: true,
    3: false,
  });

  const toggleLike = (id: number) => {
    setLikedPosts((prev) => ({
      ...prev,
      [id]: !prev[id],
    }));
  };

  return (
    <div className="min-h-screen bg-neutral-200 flex items-center justify-center p-4 sm:p-8 font-sans">
      {/* Phone Shell */}
      <div className="relative w-full max-w-[390px] h-[844px] bg-[#fdf8f0] rounded-[3rem] shadow-2xl border-[12px] border-slate-900 overflow-hidden flex flex-col text-[#432c1a]">
        
        {/* Header */}
        <header className="absolute top-0 w-full z-20 bg-[#fdf8f0]/90 backdrop-blur-md px-6 pt-12 pb-4 flex items-center justify-between border-b border-orange-900/10">
          <div className="text-xl font-bold tracking-tight text-orange-600 flex items-center gap-2">
            <span>✨</span> Networkee
          </div>
          <button className="p-2 -mr-2 text-orange-900/60 hover:text-orange-600 transition-colors">
            <Search size={22} strokeWidth={2.5} />
          </button>
        </header>

        {/* Scrollable Feed */}
        <main className="flex-1 overflow-y-auto pb-24 pt-[88px] hide-scrollbar">
          
          {/* New Post Area */}
          <div className="px-5 py-4 bg-white/50 border-b border-orange-900/5 mb-2">
            <div className="flex gap-3 items-center">
              <div className="w-10 h-10 rounded-full bg-gradient-to-tr from-orange-400 to-amber-300 flex-shrink-0 border-2 border-white shadow-sm" />
              <div className="flex-1 bg-[#fdf8f0] rounded-full h-10 px-4 flex items-center text-[#432c1a]/40 text-sm border border-orange-900/10">
                Quoi de neuf, Camille ?
              </div>
              <button className="text-orange-500 p-2">
                <ImageIcon size={22} />
              </button>
            </div>
          </div>

          {/* Posts Feed */}
          <div className="flex flex-col gap-2">
            
            {/* Post 1 */}
            <article className="bg-white/60 p-5 border-y border-orange-900/5">
              <div className="flex justify-between items-center mb-3">
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 rounded-full bg-gradient-to-br from-rose-400 to-orange-400 p-[2px]">
                    <div className="w-full h-full rounded-full border-2 border-white bg-white/20 flex items-center justify-center text-xs font-bold text-white">
                      L
                    </div>
                  </div>
                  <div>
                    <h3 className="font-semibold text-sm">Léo Dubois</h3>
                    <p className="text-xs text-[#432c1a]/50">Il y a 2h</p>
                  </div>
                </div>
                <button className="text-[#432c1a]/40">
                  <MoreHorizontal size={20} />
                </button>
              </div>
              
              <p className="text-sm mb-3 leading-relaxed">
                Petit café en terrasse pour bien commencer la journée ☕️🥐 
                Le soleil est enfin de retour à Paris !
              </p>

              {/* Photo Placeholder */}
              <div className="w-full aspect-[4/5] rounded-2xl bg-gradient-to-tr from-amber-500 via-orange-400 to-rose-400 mb-4 shadow-sm flex items-center justify-center relative overflow-hidden">
                <div className="absolute inset-0 opacity-20 mix-blend-overlay bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-white to-transparent" />
              </div>

              {/* Actions */}
              <div className="flex items-center gap-5 mb-4 text-[#432c1a]/60">
                <button 
                  onClick={() => toggleLike(1)}
                  className={`flex items-center gap-1.5 transition-colors ${likedPosts[1] ? 'text-orange-500' : 'hover:text-orange-500'}`}
                >
                  <Heart size={22} className={likedPosts[1] ? 'fill-current' : ''} />
                  <span className="text-sm font-medium">{likedPosts[1] ? '24' : '23'}</span>
                </button>
                <button className="flex items-center gap-1.5 hover:text-orange-500 transition-colors">
                  <MessageCircle size={22} />
                  <span className="text-sm font-medium">2</span>
                </button>
                <button className="flex items-center gap-1.5 ml-auto hover:text-orange-500 transition-colors">
                  <Share2 size={20} />
                </button>
              </div>

              {/* Comments */}
              <div className="space-y-2 mt-2">
                <div className="text-sm flex gap-2">
                  <span className="font-semibold">chloe_b</span>
                  <span className="text-[#432c1a]/80">Profite bien du soleil ! 😎</span>
                </div>
                <div className="text-sm flex gap-2">
                  <span className="font-semibold">maxime.t</span>
                  <span className="text-[#432c1a]/80">J'arrive !! Garde moi une place.</span>
                </div>
              </div>
            </article>

            {/* Post 2 */}
            <article className="bg-white/60 p-5 border-y border-orange-900/5">
              <div className="flex justify-between items-center mb-3">
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-purple-400 p-[2px]">
                    <div className="w-full h-full rounded-full border-2 border-white bg-white/20 flex items-center justify-center text-xs font-bold text-white">
                      M
                    </div>
                  </div>
                  <div>
                    <h3 className="font-semibold text-sm">Marie Curie</h3>
                    <p className="text-xs text-[#432c1a]/50">Il y a 5h</p>
                  </div>
                </div>
                <button className="text-[#432c1a]/40">
                  <MoreHorizontal size={20} />
                </button>
              </div>
              
              <p className="text-sm mb-3 leading-relaxed">
                Incroyable expo au Centre Pompidou cet après-midi. Je recommande à 100% l'installation lumineuse au dernier étage 🎨✨
              </p>

              {/* Photo Placeholder */}
              <div className="w-full aspect-video rounded-2xl bg-gradient-to-br from-purple-500 via-pink-500 to-orange-400 mb-4 shadow-sm flex items-center justify-center relative overflow-hidden">
                <div className="absolute top-0 right-0 w-32 h-32 bg-white/30 rounded-full blur-2xl transform translate-x-1/2 -translate-y-1/2" />
                <div className="absolute bottom-0 left-0 w-32 h-32 bg-black/10 rounded-full blur-2xl transform -translate-x-1/2 translate-y-1/2" />
              </div>

              {/* Actions */}
              <div className="flex items-center gap-5 mb-4 text-[#432c1a]/60">
                <button 
                  onClick={() => toggleLike(2)}
                  className={`flex items-center gap-1.5 transition-colors ${likedPosts[2] ? 'text-orange-500' : 'hover:text-orange-500'}`}
                >
                  <Heart size={22} className={likedPosts[2] ? 'fill-current' : ''} />
                  <span className="text-sm font-medium">{likedPosts[2] ? '128' : '127'}</span>
                </button>
                <button className="flex items-center gap-1.5 hover:text-orange-500 transition-colors">
                  <MessageCircle size={22} />
                  <span className="text-sm font-medium">5</span>
                </button>
                <button className="flex items-center gap-1.5 ml-auto hover:text-orange-500 transition-colors">
                  <Share2 size={20} />
                </button>
              </div>

              {/* Comments */}
              <div className="space-y-2 mt-2">
                <div className="text-sm flex gap-2">
                  <span className="font-semibold">arthur_art</span>
                  <span className="text-[#432c1a]/80">Les couleurs sont folles en vrai !</span>
                </div>
              </div>
            </article>

            {/* Post 3 */}
            <article className="bg-white/60 p-5 border-y border-orange-900/5">
              <div className="flex justify-between items-center mb-3">
                <div className="flex items-center gap-3">
                  <div className="w-10 h-10 rounded-full bg-gradient-to-br from-teal-400 to-emerald-400 p-[2px]">
                    <div className="w-full h-full rounded-full border-2 border-white bg-white/20 flex items-center justify-center text-xs font-bold text-white">
                      T
                    </div>
                  </div>
                  <div>
                    <h3 className="font-semibold text-sm">Thomas L.</h3>
                    <p className="text-xs text-[#432c1a]/50">Hier</p>
                  </div>
                </div>
                <button className="text-[#432c1a]/40">
                  <MoreHorizontal size={20} />
                </button>
              </div>
              
              <p className="text-sm mb-4 leading-relaxed">
                Qui est chaud pour un foot ce week-end ? Il manque 2 joueurs pour dimanche matin à Vincennes ⚽️
              </p>

              {/* Actions */}
              <div className="flex items-center gap-5 mb-4 text-[#432c1a]/60">
                <button 
                  onClick={() => toggleLike(3)}
                  className={`flex items-center gap-1.5 transition-colors ${likedPosts[3] ? 'text-orange-500' : 'hover:text-orange-500'}`}
                >
                  <Heart size={22} className={likedPosts[3] ? 'fill-current' : ''} />
                  <span className="text-sm font-medium">{likedPosts[3] ? '15' : '14'}</span>
                </button>
                <button className="flex items-center gap-1.5 hover:text-orange-500 transition-colors">
                  <MessageCircle size={22} />
                  <span className="text-sm font-medium">8</span>
                </button>
                <button className="flex items-center gap-1.5 ml-auto hover:text-orange-500 transition-colors">
                  <Share2 size={20} />
                </button>
              </div>
            </article>

            {/* Pagination / End */}
            <div className="py-8 flex justify-center items-center flex-col gap-2 opacity-50">
              <div className="w-1.5 h-1.5 rounded-full bg-orange-900/30" />
              <div className="w-1.5 h-1.5 rounded-full bg-orange-900/20" />
              <div className="w-1.5 h-1.5 rounded-full bg-orange-900/10" />
              <p className="text-xs mt-2 font-medium">Vous êtes à jour ✨</p>
            </div>
            
          </div>
        </main>

        {/* Bottom Tab Bar */}
        <nav className="absolute bottom-0 w-full bg-[#fdf8f0] pb-8 pt-4 px-6 border-t border-orange-900/10 flex justify-between items-center text-[#432c1a]/40 z-20">
          <button className="p-2 text-orange-500 flex flex-col items-center gap-1">
            <Home size={24} strokeWidth={2.5} className="fill-orange-500/20" />
          </button>
          <button className="p-2 hover:text-orange-500 transition-colors">
            <Search size={24} strokeWidth={2.5} />
          </button>
          <button className="p-3 bg-orange-500 text-white rounded-2xl hover:bg-orange-600 transition-colors shadow-lg shadow-orange-500/30 -mt-6">
            <PlusSquare size={24} strokeWidth={2.5} />
          </button>
          <button className="p-2 hover:text-orange-500 transition-colors">
            <Heart size={24} strokeWidth={2.5} />
          </button>
          <button className="p-2 hover:text-orange-500 transition-colors">
            <User size={24} strokeWidth={2.5} />
          </button>
        </nav>
        
        {/* Home Indicator */}
        <div className="absolute bottom-2 left-1/2 -translate-x-1/2 w-32 h-1.5 bg-[#432c1a]/20 rounded-full z-30" />
        
      </div>
      
      {/* Global Style overrides to hide scrollbars for cleaner mobile look */}
      <style dangerouslySetInnerHTML={{__html: `
        .hide-scrollbar::-webkit-scrollbar {
          display: none;
        }
        .hide-scrollbar {
          -ms-overflow-style: none;
          scrollbar-width: none;
        }
      `}} />
    </div>
  );
}
