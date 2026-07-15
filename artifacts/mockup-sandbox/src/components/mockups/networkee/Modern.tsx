import React, { useState } from 'react';
import { Heart, MessageCircle, MoreHorizontal, Image as ImageIcon, Smile, Send, ChevronLeft, ChevronRight } from 'lucide-react';

export function Modern() {
  const [likes, setLikes] = useState<Record<number, boolean>>({ 1: true, 2: false, 3: false });

  const toggleLike = (id: number) => {
    setLikes(prev => ({ ...prev, [id]: !prev[id] }));
  };

  return (
    <div className="min-h-screen bg-slate-50 font-sans text-slate-900 pb-20">
      {/* Navbar */}
      <nav className="sticky top-0 z-50 bg-white/80 backdrop-blur-md border-b border-slate-200">
        <div className="max-w-5xl mx-auto px-4 h-16 flex items-center justify-between">
          <div className="flex items-center gap-2">
            <div className="w-8 h-8 rounded-xl bg-teal-600 flex items-center justify-center text-white font-bold text-lg shadow-sm shadow-teal-600/20">
              N
            </div>
            <span className="font-bold text-xl tracking-tight text-slate-900">Networkee</span>
          </div>
          
          <div className="hidden sm:flex items-center gap-6 font-medium text-sm text-slate-600">
            <a href="#" className="hover:text-teal-600 transition-colors">Home</a>
            <a href="#" className="hover:text-teal-600 transition-colors">Profil</a>
            <a href="#" className="text-teal-600 flex items-center gap-1">
              Le Fil 🌈
            </a>
            <a href="#" className="hover:text-red-500 transition-colors ml-4 border-l border-slate-200 pl-4">Bye 👋</a>
          </div>
        </div>
      </nav>

      {/* Main Content */}
      <main className="max-w-2xl mx-auto px-4 pt-8">
        
        {/* New Post Area */}
        <div className="bg-white rounded-2xl shadow-sm border border-slate-100 p-5 mb-8">
          <div className="flex gap-4">
            <div className="w-10 h-10 rounded-full bg-gradient-to-br from-teal-400 to-emerald-500 shrink-0 border-2 border-white shadow-sm flex items-center justify-center text-white font-semibold text-sm">
              Moi
            </div>
            <div className="flex-1">
              <textarea 
                className="w-full bg-slate-50 hover:bg-slate-100/50 focus:bg-white transition-colors rounded-xl border border-transparent focus:border-teal-200 outline-none resize-none p-3 text-slate-700 placeholder:text-slate-400 focus:ring-4 focus:ring-teal-500/10"
                rows={2}
                placeholder="Quoi de neuf aujourd'hui ?"
              ></textarea>
              <div className="flex items-center justify-between mt-3">
                <div className="flex gap-1 text-slate-400">
                  <button className="p-2 hover:bg-slate-100 rounded-lg transition-colors hover:text-teal-600">
                    <ImageIcon className="w-5 h-5" />
                  </button>
                  <button className="p-2 hover:bg-slate-100 rounded-lg transition-colors hover:text-teal-600">
                    <Smile className="w-5 h-5" />
                  </button>
                </div>
                <button className="bg-teal-600 hover:bg-teal-700 text-white px-5 py-2.5 rounded-xl font-medium text-sm flex items-center gap-2 transition-all active:scale-95 shadow-sm shadow-teal-600/20">
                  <span>Publier</span>
                  <Send className="w-4 h-4" />
                </button>
              </div>
            </div>
          </div>
        </div>

        {/* Feed */}
        <div className="space-y-6">
          
          {/* Post 1 */}
          <article className="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden transition-shadow hover:shadow-md">
            <div className="p-5">
              <div className="flex justify-between items-start mb-4">
                <div className="flex gap-3 items-center">
                  <div className="w-10 h-10 rounded-full bg-gradient-to-tr from-blue-500 to-indigo-500 flex items-center justify-center text-white font-semibold shadow-sm">
                    AD
                  </div>
                  <div>
                    <h3 className="font-semibold text-slate-900 leading-tight">Alexandre Dupont</h3>
                    <p className="text-xs text-slate-500 mt-0.5">Il y a 2 heures</p>
                  </div>
                </div>
                <button className="text-slate-400 hover:bg-slate-100 p-2 rounded-full transition-colors">
                  <MoreHorizontal className="w-5 h-5" />
                </button>
              </div>
              
              <p className="text-slate-700 leading-relaxed text-[15px]">
                Salut tout le monde ! Trop content de vous retrouver sur Networkee 2.0 🚀 La nouvelle interface est vraiment dingue, non ? Plus claire, plus rapide... on adore !
              </p>
            </div>
            
            <div className="px-5 py-3 border-t border-slate-50 flex gap-6">
              <button 
                onClick={() => toggleLike(1)}
                className={`flex items-center gap-2 text-sm font-medium transition-colors ${likes[1] ? 'text-teal-600' : 'text-slate-500 hover:text-teal-600'}`}
              >
                <Heart className={`w-5 h-5 ${likes[1] ? 'fill-current' : ''}`} />
                <span>{likes[1] ? 13 : 12}</span>
              </button>
              <button className="flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-teal-600 transition-colors">
                <MessageCircle className="w-5 h-5" />
                <span>2</span>
              </button>
            </div>

            <div className="bg-slate-50/80 px-5 py-4 space-y-4 border-t border-slate-100">
              <div className="flex gap-3">
                <div className="w-8 h-8 rounded-full bg-gradient-to-tr from-pink-400 to-orange-400 shrink-0 flex items-center justify-center text-white text-xs font-semibold shadow-sm">C</div>
                <div className="flex-1 bg-white p-3.5 rounded-2xl rounded-tl-none shadow-sm border border-slate-100/50 text-[14px]">
                  <p className="font-semibold text-slate-900 mb-1">Camille</p>
                  <p className="text-slate-600">Grave ! Ça change du vieux Bootstrap 😂 On revit.</p>
                </div>
              </div>
              <div className="flex gap-3">
                <div className="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-400 to-cyan-400 shrink-0 flex items-center justify-center text-white text-xs font-semibold shadow-sm">L</div>
                <div className="flex-1 bg-white p-3.5 rounded-2xl rounded-tl-none shadow-sm border border-slate-100/50 text-[14px]">
                  <p className="font-semibold text-slate-900 mb-1">Lucas</p>
                  <p className="text-slate-600">Clair, j'adore les nouvelles couleurs, hyper clean.</p>
                </div>
              </div>
            </div>
          </article>

          {/* Post 2 */}
          <article className="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden transition-shadow hover:shadow-md">
            <div className="p-5">
              <div className="flex justify-between items-start mb-4">
                <div className="flex gap-3 items-center">
                  <div className="w-10 h-10 rounded-full bg-gradient-to-br from-rose-400 to-red-500 flex items-center justify-center text-white font-semibold shadow-sm">
                    SM
                  </div>
                  <div>
                    <h3 className="font-semibold text-slate-900 leading-tight">Sophie Martin</h3>
                    <p className="text-xs text-slate-500 mt-0.5">Il y a 5 heures</p>
                  </div>
                </div>
                <button className="text-slate-400 hover:bg-slate-100 p-2 rounded-full transition-colors">
                  <MoreHorizontal className="w-5 h-5" />
                </button>
              </div>
              
              <p className="text-slate-700 leading-relaxed text-[15px] mb-4">
                Petite balade en forêt ce matin pour se ressourcer. Rien de tel pour commencer la semaine ! 🌲🍃
              </p>

              <div className="w-full aspect-[4/3] sm:aspect-video rounded-xl bg-gradient-to-tr from-emerald-800 via-teal-700 to-emerald-500 overflow-hidden flex items-center justify-center shadow-inner relative">
                 <div className="absolute inset-0 opacity-20 mix-blend-overlay" style={{backgroundImage: 'radial-gradient(circle at 20% 30%, rgba(255,255,255,0.4) 0%, transparent 50%)'}}></div>
                 <div className="absolute bottom-0 left-0 right-0 h-1/3 bg-gradient-to-t from-black/20 to-transparent"></div>
              </div>
            </div>
            
            <div className="px-5 py-3 border-t border-slate-50 flex gap-6">
              <button 
                onClick={() => toggleLike(2)}
                className={`flex items-center gap-2 text-sm font-medium transition-colors ${likes[2] ? 'text-teal-600' : 'text-slate-500 hover:text-teal-600'}`}
              >
                <Heart className={`w-5 h-5 ${likes[2] ? 'fill-current' : ''}`} />
                <span>{likes[2] ? 35 : 34}</span>
              </button>
              <button className="flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-teal-600 transition-colors">
                <MessageCircle className="w-5 h-5" />
                <span>1</span>
              </button>
            </div>

            <div className="bg-slate-50/80 px-5 py-4 border-t border-slate-100">
              <div className="flex gap-3">
                <div className="w-8 h-8 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 shrink-0 flex items-center justify-center text-white text-xs font-semibold shadow-sm">E</div>
                <div className="flex-1 bg-white p-3.5 rounded-2xl rounded-tl-none shadow-sm border border-slate-100/50 text-[14px]">
                  <p className="font-semibold text-slate-900 mb-1">Emma</p>
                  <p className="text-slate-600">Magnifique ! Tu étais vers où ? La lumière est folle.</p>
                </div>
              </div>
            </div>
          </article>

          {/* Post 3 */}
          <article className="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden transition-shadow hover:shadow-md">
            <div className="p-5">
              <div className="flex justify-between items-start mb-4">
                <div className="flex gap-3 items-center">
                  <div className="w-10 h-10 rounded-full bg-gradient-to-br from-slate-700 to-slate-900 flex items-center justify-center text-white font-semibold shadow-sm">
                    HL
                  </div>
                  <div>
                    <h3 className="font-semibold text-slate-900 leading-tight">Hugo L.</h3>
                    <p className="text-xs text-slate-500 mt-0.5">Hier à 14:30</p>
                  </div>
                </div>
                <button className="text-slate-400 hover:bg-slate-100 p-2 rounded-full transition-colors">
                  <MoreHorizontal className="w-5 h-5" />
                </button>
              </div>
              
              <p className="text-slate-700 leading-relaxed text-[15px]">
                Qui est chaud pour un café cet aprem ? Je suis dans le 11ème, on se capte ? ☕️☀️
              </p>
            </div>
            
            <div className="px-5 py-3 border-t border-slate-50 flex gap-6">
              <button 
                onClick={() => toggleLike(3)}
                className={`flex items-center gap-2 text-sm font-medium transition-colors ${likes[3] ? 'text-teal-600' : 'text-slate-500 hover:text-teal-600'}`}
              >
                <Heart className={`w-5 h-5 ${likes[3] ? 'fill-current' : ''}`} />
                <span>{likes[3] ? 6 : 5}</span>
              </button>
              <button className="flex items-center gap-2 text-sm font-medium text-slate-500 hover:text-teal-600 transition-colors">
                <MessageCircle className="w-5 h-5" />
                <span>0</span>
              </button>
            </div>
          </article>
          
        </div>

        {/* Pagination */}
        <div className="mt-10 flex items-center justify-between bg-white px-5 py-3 rounded-xl border border-slate-200 shadow-sm">
          <button className="flex items-center gap-1.5 text-sm font-medium text-slate-400 transition-colors cursor-not-allowed">
            <ChevronLeft className="w-4 h-4" />
            <span className="hidden sm:inline">Précédent</span>
          </button>
          
          <div className="flex items-center gap-1">
            <button className="w-9 h-9 flex items-center justify-center rounded-lg bg-teal-50 text-teal-700 font-semibold text-sm">1</button>
            <button className="w-9 h-9 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-600 font-medium text-sm transition-colors">2</button>
            <button className="w-9 h-9 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-600 font-medium text-sm transition-colors">3</button>
            <span className="px-2 text-slate-400">...</span>
            <button className="w-9 h-9 flex items-center justify-center rounded-lg hover:bg-slate-100 text-slate-600 font-medium text-sm transition-colors">12</button>
          </div>

          <button className="flex items-center gap-1.5 text-sm font-medium text-slate-600 hover:text-teal-600 transition-colors">
            <span className="hidden sm:inline">Suivant</span>
            <ChevronRight className="w-4 h-4" />
          </button>
        </div>

      </main>
    </div>
  );
}

export default Modern;