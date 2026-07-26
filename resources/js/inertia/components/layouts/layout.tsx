import React from "react";
import { SidebarInset, SidebarProvider } from "@/components/ui/sidebar";
import { usePage } from "@inertiajs/react";
import { AppSidebar } from "@/components/dashboard/sidebar/app-sidebar";
import { SiteHeader } from "@/components/dashboard/navbar/site-header";
import { TooltipProvider } from "@/components/ui/tooltip";
import { ThemeProvider } from "@/providers/theme-provider";
import ToasterProvider from "@/providers/toaster-provider";

const Layout = ({ children }: { children?: React.ReactNode }) => {
  const {
    props: { currentLocale },
  } = usePage();

  return (
    <ThemeProvider defaultTheme={"system"}>
      <TooltipProvider>
        <ToasterProvider>
          <SidebarProvider
            style={
              {
                "--sidebar-width": "calc(var(--spacing) * 72)",
                "--header-height": "calc(var(--spacing) * 12)",
              } as React.CSSProperties
            }
          >
            <AppSidebar
              side={`${currentLocale}` == "ar" ? "right" : "left"}
              collapsible={"icon"}
              variant="inset"
            />
            <SidebarInset>
              <SiteHeader />
              <div className="flex flex-1 flex-col">
                <div className="@container/main flex flex-1 flex-col gap-2">
                  <div className="flex flex-col gap-4 p-4 md:gap-6 md:py-6">
                    {children}
                  </div>
                </div>
              </div>
            </SidebarInset>
          </SidebarProvider>
        </ToasterProvider>
      </TooltipProvider>
    </ThemeProvider>
  );
};

export default Layout;
