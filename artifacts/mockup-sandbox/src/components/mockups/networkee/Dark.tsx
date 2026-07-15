import React, { useState } from 'react';
import { Heart, MessageCircle, Share2, Send, Image as ImageIcon, Smile, MoreHorizontal, LogOut, User, Home, Hash } from 'lucide-react';

export function Dark() {
  return (
    <div className="min-h-screen bg-[#0f0f13] text-zinc-100 font-sans selection:bg-violet-500/30 pb-20">
      {/* Navbar */}
      <nav className="sticky top-0 z-50 w-full bg-[#0a0a0d]/80 backdrop-blur-xl border-b border-white/[0.04]">
        <div className="max-w-4xl mx-auto px-4 h-16 flex items-center justify-between">
          <div className="flex items-center gap-3">
            <div className="w-9 h-9 rounded-xl bg-gradient-to-br from-violet-600 to-indigo-600 flex items-center justify-center font-bold text-white shadow-[0_0_15px_rgba(124,58,237,0.4)]">
              N
            </div>
            <span className="text-xl font-bold tracking-tight text-white">
              Networkee
            </span>
          </div>
          <div className="hidden sm:flex items-center gap-2">
            <NavItem icon={<Home size={18} />} label="Accueil" />
            <NavItem icon={<User size={18} />} label="Profil" />
            <NavItem icon={<Hash size={18} />} label="Le Fil 🌈" active />
            <div className="w-[1px] h-6 bg-white/10 mx-2" />
            <NavItem icon={<LogOut size={18} />} label="Bye 👋" className="text-zinc-400 hover:text-rose-400 hover:bg-rose-500/10" />
          </div>
        </div>
      </nav>

      {/* Main Content */}
      <main className="max-w-2xl mx-auto px-4 py-8 flex flex-col gap-8">
        
        {/* New Post */}
        <div className="bg-[#1a1a24] rounded-2xl p-5 border border-white/[0.05] shadow-[0_4px_20px_rgba(0,0,0,0.3)] transition-all hover:border-violet-500/30 hover:shadow-[0_4px_30px_rgba(124,58,237,0.06)] relative overflow-hidden group">
          <div className="absolute top-0 left-0 w-full h-[1px] bg-gradient-to-r from-transparent via-violet-500/20 to-transparent group-hover:via-violet-400/50 transition-all duration-700"></div>
          <div className="flex gap-4">
            <div className="w-11 h-11 rounded-full bg-gradient-to-tr from-fuchsia-500 to-violet-500 flex-shrink-0 border border-white/10 shadow-[0_0_10px_rgba(168,85,247,0.2)]" />
            <div className="flex-1 space-y-3">
              <textarea 
                className="w-full bg-transparent border-none resize-none focus:ring-0 text-lg placeholder:text-zinc-600 min-h-[80px] p-0 text-zinc-200"
                placeholder="Quoi de neuf aujourd'hui ?"
              ></textarea>
              <div className="flex items-center justify-between pt-3 border-t border-white/[0.05]">
                <div className="flex gap-1">
                  <button className="p-2.5 text-violet-400 hover:bg-violet-500/15 rounded-xl transition-colors cursor-pointer">
                    <ImageIcon size={20} />
                  </button>
                  <button className="p-2.5 text-violet-400 hover:bg-violet-500/15 rounded-xl transition-colors cursor-pointer">
                    <Smile size={20} />
                  </button>
                </div>
                <button className="bg-violet-600 hover:bg-violet-500 text-white px-5 py-2.5 rounded-xl font-medium transition-all shadow-[0_0_15px_rgba(124,58,237,0.4)] hover:shadow-[0_0_25px_rgba(124,58,237,0.6)] flex items-center gap-2 cursor-pointer active:scale-95">
                  <span>Publier</span>
                  <Send size={16} />
                </button>
              </div>
            </div>
          </div>
        </div>

        {/* Feed */}
        <div className="flex flex-col gap-6">
          <Post 
            author="Camille Dubois"
            handle="@camilled"
            avatarColor="from-blue-500 to-emerald-400"
            time="Il y a 2 heures"
            content="Petite balade dans Paris aujourd'hui. La lumière était incroyable ! ✨ Quelqu'un pour un café ce week-end ?"
            imageGradient="from-indigo-950 via-purple-950 to-slate-900"
            likes={42}
            comments={[
              { author: "Lucas", text: "Grave chaud pour le café ! Dimanche 15h ?" }
            ]}
          />

          <Post 
            author="Julien M."
            handle="@julienm"
            avatarColor="from-amber-400 to-orange-500"
            time="Il y a 4 heures"
            content="Je viens de terminer la saison 3 de The Bear... C'est un chef-d'œuvre absolu. 🐻👨‍🍳 Le rythme, la tension, tout est parfait."
            likes={18}
            comments={[
              { author: "Sophie", text: "C'est sur ma liste !! Pas de spoilers stp 🙈" },
              { author: "Thomas", text: "L'épisode 6 m'a mis une claque monumentale." }
            ]}
          />

          <Post 
            author="Éléonore"
            handle="@eleonore_art"
            avatarColor="from-pink-500 to-rose-400"
            time="Hier"
            content="Nouveau projet d'UI sur lequel je bosse depuis des semaines. J'ai décidé de partir sur un mode sombre très profond avec des accents néon. Vous en pensez quoi ? 🎨🚀"
            imageGradient="from-[#0f0f13] via-[#2a1a4a] to-[#4a1a5a]"
            likes={156}
            comments={[]}
          />
        </div>

        {/* Pagination */}
        <div className="flex justify-center items-center gap-2 pt-8 pb-4">
          <button className="px-4 py-2 rounded-xl bg-[#1a1a24] border border-white/[0.05] text-zinc-400 hover:text-white hover:border-violet-500/30 transition-all font-medium text-sm cursor-pointer hover:bg-white/[0.02]">Précédent</button>
          <div className="flex items-center gap-1.5 px-2">
            <button className="w-9 h-9 rounded-xl bg-violet-600/20 border border-violet-500/50 text-violet-300 font-medium flex items-center justify-center cursor-pointer">1</button>
            <button className="w-9 h-9 rounded-xl bg-transparent text-zinc-400 hover:bg-white/[0.04] hover:text-white transition-all flex items-center justify-center cursor-pointer">2</button>
            <button className="w-9 h-9 rounded-xl bg-transparent text-zinc-400 hover:bg-white/[0.04] hover:text-white transition-all flex items-center justify-center cursor-pointer">3</button>
            <span className="text-zinc-600 px-1">...</span>
          </div>
          <button className="px-4 py-2 rounded-xl bg-[#1a1a24] border border-white/[0.05] text-zinc-400 hover:text-white hover:border-violet-500/30 transition-all font-medium text-sm cursor-pointer hover:bg-white/[0.02]">Suivant</button>
        </div>

      </main>
    </div>
  );
}

function NavItem({ icon, label, active, className = "" }: { icon: React.ReactNode, label: string, active?: boolean, className?: string }) {
  return (
    <a href="#" className={`flex items-center gap-2 px-3 py-2 rounded-xl transition-all ${active ? 'text-violet-300 bg-violet-500/15' : 'text-zinc-400 hover:text-zinc-100 hover:bg-white/5'} ${className}`}>
      {icon}
      <span className="font-medium text-sm">{label}</span>
    </a>
  );
}

function Post({ author, handle, avatarColor, time, content, imageGradient, likes, comments }: { author: string, handle: string, avatarColor: string, time: string, content: string, imageGradient?: string, likes: number, comments: Array<{author: string, text: string}> }) {
  const [liked, setLiked] = useState(false);

  return (
    <article className="bg-[#1a1a24] rounded-2xl border border-white/[0.04] shadow-lg overflow-hidden transition-all hover:border-white/[0.08]">
      {/* Post Header */}
      <div className="p-5 flex items-center justify-between">
        <div className="flex items-center gap-3">
          <div className={`w-11 h-11 rounded-full bg-gradient-to-tr ${avatarColor} border border-white/10`} />
          <div>
            <div className="flex items-center gap-2">
              <span className="font-semibold text-zinc-100">{author}</span>
              <span className="text-sm text-zinc-500">{handle}</span>
            </div>
            <div className="text-xs text-zinc-500 mt-0.5">{time}</div>
          </div>
        </div>
        <button className="text-zinc-500 hover:text-zinc-200 p-2 rounded-xl hover:bg-white/5 transition-colors cursor-pointer">
          <MoreHorizontal size={20} />
        </button>
      </div>

      {/* Post Content */}
      <div className="px-5 pb-4">
        <p className="text-zinc-300 leading-relaxed whitespace-pre-wrap">{content}</p>
      </div>

      {/* Optional Image */}
      {imageGradient && (
        <div className="px-5 pb-4">
          <div className={`w-full aspect-[16/10] rounded-xl bg-gradient-to-br ${imageGradient} border border-white/5 shadow-inner flex items-center justify-center relative overflow-hidden group`}>
            {/* Some decorative elements for the placeholder */}
            <div className="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(255,255,255,0.05)_0%,transparent_100%)] opacity-50"></div>
            <div className="w-40 h-40 rounded-full border border-white/[0.03] absolute -top-10 -right-10 mix-blend-overlay"></div>
            <div className="w-60 h-60 rounded-full border border-white/[0.03] absolute -bottom-20 -left-10 mix-blend-overlay"></div>
            <div className="w-20 h-20 rounded-full border border-white/[0.05] absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 blur-sm"></div>
            <ImageIcon size={48} className="text-white/20 group-hover:scale-110 transition-transform duration-700 ease-out relative z-10" />
          </div>
        </div>
      )}

      {/* Actions */}
      <div className="px-5 py-3 flex items-center gap-6 border-t border-white/[0.03]">
        <button 
          onClick={() => setLiked(!liked)} 
          className={`flex items-center gap-2 group transition-colors cursor-pointer ${liked ? 'text-pink-500' : 'text-zinc-400 hover:text-pink-400'}`}
        >
          <div className={`p-2 rounded-full transition-all group-hover:bg-pink-500/10 ${liked ? 'bg-pink-500/15' : ''}`}>
            <Heart size={20} className={liked ? 'fill-current scale-110' : 'transition-transform group-hover:scale-110'} />
          </div>
          <span className="font-medium text-sm">{liked ? likes + 1 : likes}</span>
        </button>
        
        <button className="flex items-center gap-2 text-zinc-400 hover:text-violet-400 group transition-colors cursor-pointer">
          <div className="p-2 rounded-full group-hover:bg-violet-500/10 transition-all">
            <MessageCircle size={20} className="transition-transform group-hover:scale-110" />
          </div>
          <span className="font-medium text-sm">{comments.length}</span>
        </button>
        
        <button className="flex items-center gap-2 text-zinc-400 hover:text-emerald-400 group transition-colors ml-auto cursor-pointer">
          <div className="p-2 rounded-full group-hover:bg-emerald-500/10 transition-all">
            <Share2 size={20} className="transition-transform group-hover:scale-110" />
          </div>
        </button>
      </div>

      {/* Comments */}
      {comments.length > 0 && (
        <div className="bg-[#0f0f13]/60 px-5 py-4 space-y-4 border-t border-white/[0.02]">
          {comments.map((comment, i) => (
            <div key={i} className="flex gap-3">
              <div className="w-8 h-8 rounded-full bg-gradient-to-br from-zinc-700 to-zinc-800 border border-white/5 flex-shrink-0" />
              <div className="flex-1">
                <div className="bg-[#1a1a24] rounded-2xl rounded-tl-sm px-4 py-2.5 border border-white/[0.03] inline-block shadow-sm">
                  <span className="font-medium text-sm text-zinc-200 block mb-0.5">{comment.author}</span>
                  <p className="text-sm text-zinc-400 leading-snug">{comment.text}</p>
                </div>
              </div>
            </div>
          ))}
        </div>
      )}
    </article>
  );
}
