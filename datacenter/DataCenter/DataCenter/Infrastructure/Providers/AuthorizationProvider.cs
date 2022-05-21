using DataCenter.Data.Models;
using DataCenter.Infrastructure.Providers.Interfaces;
using System.Collections.Generic;

namespace DataCenter.Infrastructure.Providers
{
    public class AuthorizationProvider : IAuthorizationProvider, IProvider
    {
        private readonly IDatabaseProvider databaseProvider;

        private List<string> tokens = new List<string>();

        public AuthorizationProvider(DatabaseProvider databaseProvider)
        {
            this.databaseProvider = databaseProvider;
        }

        public void RestoreCredentials()
        {
            tokens = databaseProvider.GetAll<Credentials, string>(c => c.GeneratedToken);
        }

        public bool Authorize(string accessToken)
        {
            return tokens.Contains(accessToken);
        }

        public void AddAccessToken(string accessToken)
        {
            if (!tokens.Contains(accessToken))
            {
                tokens.Add(accessToken);
            }
        }

        public void RemoveAccessToken(string accessToken)
        {
            if (tokens.Contains(accessToken))
            {
                tokens.Remove(accessToken);
            }
        }
    }
}